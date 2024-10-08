<?php
namespace Poznavacky\Models\Processors;

use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\DatabaseItems\Natural;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Logger;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\Settings;
use Poznavacky\Models\Statics\UserManager;

/**
 * Třída starající se o zpracování dat odeslaných z formuláře pro přidání obrázku a případné uložení obrázku do databáze
 * @author Jan Štěch
 */
class PictureAdder
{
    
    private const TEMP_THUMB_DOMAINS = array(
        'data:image/jpeg;base64',
        'external-content.duckduckgo.com',
        'th.bing.com',
        'avatars.mds.yandex.net'
    );

    private ClassObject $class;

    
    /**
     * Konstruktor třídy nastavující objekt (studijní) třídy, do které bude tato třída přidávat obrázky
     * @param ClassObject $class Objekt třídy
     */
    public function __construct(ClassObject $class)
    {
        $this->class = $class;
    }
    
    /**
     * Metoda zpracovávající data odeslaná z formuláře na stránce pro přidávání obrázků
     * Data jsou ověřena a posléze i uložena do databáze, nebo je vyvolána výjimka s chybovou hláškou
     * @param array $POSTdata Pole dat odeslaných z formuláře
     * @return boolean TRUE, pokud vše proběhne tak, jak má
     * @throws AccessDeniedException Pokud nejsou poskytnutá data v pořádku nebo se vyskytne jiná chyba
     * @throws DatabaseException
     */
    public function processFormData(array $POSTdata): bool
    {
        $naturalId = $POSTdata['naturalId'];
        $url = trim($POSTdata['url']); //Ořež mezery
        
        $natural = $this->checkData($naturalId, $url);    //Kontrola dat
        return $this->addPicture($natural, $url);           //Ovlivnění databáze
    }
    
    /**
     * Metoda ověřující, zda jsou poskytnutá data v pořádku
     * @param int $naturalId ID přírodniny, ke které chceme přidat obrázek
     * @param string $url Adresa přidávaného obrázku
     * @return Natural Objekt reprezentující přírodninu, ke které hodláme přidat nový obrázek, pokud jsou data v pořádku
     * @throws DatabaseException
     * @throws AccessDeniedException V případě že data nesplňují podmínky
     */
    public function checkData(int $naturalId, string $url): Natural
    {
        $naturals = $this->class->getNaturals();

        $natural = array_filter($naturals, function($natural) use ($naturalId) {
            return $natural->getId() === $naturalId;
        });

        //Přírodnina s tímto názvem ve zvolené třídě neexistuje
        if (empty($natural)) {
            (new Logger())->warning('Uživatel s ID {userId} se pokusil přidat nebo upravit obrázek do/v třídy/třídě s ID {classId} z IP adresy {ip}, avšak zvolil neznámou přírodninu s ID ({naturalId})',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->class->getId(),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'naturalId' => $naturalId
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_ADD_PICTURE_UNKNOWN_NATURAL, null, null);
        }
        $natural = array_shift($natural);

        //Ověření, zda adresa vede na obrázek (kód inspirovaný odpovědí na StackOverflow: https://stackoverflow.com/a/24936993)
        $typeCheck = false;
        $type = null;
        
        $url_headers = @get_headers($url, 1, stream_context_create(array('http' => array('header' => implode("\r\n", array("User-Agent: Poznávačky.com image type checker"))))));
        //Pokud je cílová URL adresa přesměrováním, obsahuje $url_headers elementy pro jednotlivé "skoky"
        //Zajímá nás status kód a typ posledního skoku (konečné stránky)
        if (isset($url_headers['Content-Type']) || isset($url_headers['content-type'])) {
            $statusCode = substr($url_headers[max(array_filter(array_keys($url_headers), 'is_numeric'))], 9, 3);
            $type = $url_headers['Content-Type'] ?? $url_headers['content-type'];
            if (is_array($type)) {
                $type = @strtolower($type[count($type) - 1]);
            }
            if ($statusCode >= 400) {
                $typeCheck = null;
            }
            if (in_array($type, Settings::ALLOWED_IMAGE_TYPES)) {
                $typeCheck = true;
            }
        }
        
        if (is_null($typeCheck)) {
            (new Logger())->notice('Uživatel s ID {userId} se pokusil přidat nebo upravit obrázek do/v třídy/třídě s ID {classId} k přírodnině s ID {naturalId} z IP adresy {ip}, avšak obrázek nemohl být načten (zadaná URL adresa: {url})',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->class->getId(),
                    'naturalId' => $natural->getId(),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'url' => $url
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_ADD_PICTURE_INVALID_URL, null, null);
        }
        if ($typeCheck === false) {
            (new Logger())->notice('Uživatel s ID {userId} se pokusil přidat nebo upravit obrázek do/v třídy/třídě s ID {classId} k přírodnině s ID {naturalId} z IP adresy {ip}, avšak obrázek byl v neakceptovaném formátu ({imageFormat})',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->class->getId(),
                    'naturalId' => $natural->getId(),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'imageFormat' => $type
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_ADD_PICTURE_INVALID_FORMAT, null, null);
        }
        
	//Ověření, že není přidáván dočasný náhled z vyhledávače
	if (in_array(false, array_map(function($domain) use($url) {return strpos($url, $domain) === false;}, self::TEMP_THUMB_DOMAINS))) {
	    (new Logger())->notice('Uživatel s ID {userId} se pokusil přidat nebo upravit obrázek do/v třídy/třídě s ID {classId} k přírodnině s ID {naturalId} z IP adresy {ip}, avšak zadaná URL adresa ({url}) vedla na dočasný náhled obrázku vygenerovaný vyhledávačem',
		array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->class->getId(),
                    'naturalId' => $natural->getId(),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'url' => $url
		));
	    throw new AccessDeniedException(AccessDeniedException::REASON_ADD_PICTURE_TEMP_THUMB_URL, null, null);
	}

        //Ověření, zda již obrázek u stejné přírodniny není nahrán
        if ($natural->pictureExists($url)) {
            (new Logger())->notice('Uživatel s ID {userId} se pokusil přidat nebo upravit obrázek do/v třídy/třídě s ID {classId} k přírodnině s ID {naturalId} z IP adresy {ip}, avšak daný obrázek už byl k přírodnině přidán ({pictureUrl})',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->class->getId(),
                    'naturalId' => $natural->getId(),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'pictureUrl' => $url
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_ADD_PICTURE_DUPLICATE_PICTURE, null, null);
        }
        
        return $natural;
    }
    
    /**
     * Metoda vkládající obrázek do databáze a zvyšující počet přidaných obrázků u přihlášeného uživatele
     * @param Natural $natural Objekt přírodniny, ke které chceme přidat obrázek
     * @param string $url Adresa přidávaného obrázku
     * @return boolean TRUE, pokud je úspěšně uložen nový obrázek
     * @throws DatabaseException Pokud se obrázek nepodaří uložit nebo navýšit uživateli počet přidaných obrázků
     * @throws AccessDeniedException Pokud není žádný uživatel přihlášen
     */
    private function addPicture(Natural $natural, string $url): bool
    {
        //Změň https na http (funguje častěji)
        $url = preg_replace("/^https:\/\//", "http://", $url);
        
        //Vložení obrázku do databáze
        try {
            $natural->addPicture($url);
			
			//Zvyš počet obrázků ve složce uložené v $_SESSION
			$aChecker = new AccessChecker();
			if ($aChecker->checkPart()) {
				$_SESSION['selection']['part']->initialize(null, null, null, null, null,
					($_SESSION['selection']['part']->getPicturesCount() + 1));
			}
        } catch (DatabaseException $e) {
            (new Logger())->alert('Uživatel s ID {userId} se pokusil přidat obrázek do třídy s ID {classId} z IP adresy {ip}, avšak neznámá chyba zabránila uložení obrázku; pokud toto nebyla ojedinělá chyba, může být vážně narušeno fungování systému',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->class->getId(),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'pictureUrl' => $url
                ));
            throw new DatabaseException(AccessDeniedException::REASON_UNEXPECTED, 0, $e);
        }
        
        //Zvýšení počtu přidaných obrázků u uživatele
        UserManager::getUser()->incrementAddedPictures();
        
        (new Logger())->info('Uživatel s ID {userId} přidal obrázek do třídy s ID {classId} k přírodnině s ID {naturalId} z IP adresy {ip}',
            array(
                'userId' => UserManager::getId(),
                'classId' => $this->class->getId(),
                'naturalId' => $natural->getId(),
                'ip' => $_SERVER['REMOTE_ADDR']
            ));
        return true;
    }
}

