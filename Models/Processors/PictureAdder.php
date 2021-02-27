<?php
namespace Poznavacky\Models\Processors;

use Poznavacky\Models\DatabaseItems\Group;
use Poznavacky\Models\DatabaseItems\Natural;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Statics\UserManager;

/** 
 * Třída starající se o zpracování dat odeslaných z formuláře pro přidání obrázku a případné uložení obrázku do databáze
 * @author Jan Štěch
 */
class PictureAdder
{
    const ALLOWED_IMAGE_TYPES = array(
        'image/png',
        'image/jpg',
        'image/jpeg',
        'image/jpe',
        'image/gif',
        'image/tif',
        'image/tiff',
        'image/svg'
    );
    
    private Group $group;
    
    /**
     * Konstruktor třídy nastavující objekt poznávačky, do které bude tato třída přidávat obrázky
     * @param Group $group Objekt poznávačky (musí patřit do třídy)
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }
    
    /**
     * Metoda zpracovávající data odeslaná z formuláře na stránce pro přidávání obrázků
     * Data jsou ověřena a posléze i uložena do databáze, nebo je vyvolána výjimka s chybovou hláškou
     * @param array $POSTdata Pole dat odeslaných z formuláře
     * @return boolean TRUE, pokud vše proběhne tak, jak má
     * @throws AccessDeniedException Pokud nejsou poskytnutá data v pořádku nebo se vyskytne jiná chyba
     */
    public function processFormData(array $POSTdata): bool
    {
        $naturalName = $POSTdata['naturalName'];
        $url = $POSTdata['url'];
        
        $natural = $this->checkData($naturalName, $url);    //Kontrola dat
        return $this->addPicture($natural, $url);           //Ovlivnění databáze
    }
    
    /**
     * Metoda ověřující, zda jsou poskytnutá data v pořádku
     * @param string $naturalName Jméno přírodniny, ke které chceme přidat obrázek
     * @param string $url Adresa přidávaného obrázku
     * @throws AccessDeniedException V případě že data nesplňují podmínky
     * @return Natural Objekt reprezentující přírodninu, ke které hodláme přidat nový obrázek, pokud jsou data v pořádku
     */
    public function checkData(string $naturalName, string $url): Natural
    {
        $naturals = $this->group->getNaturals();
        for ($i = 0; $i < count($naturals) && $naturals[$i]->getName() !== $naturalName; $i++){}
        
        //Přírodnina s tímto názvem ve zvolené poznávačce neexistuje
        if ($i === count($naturals))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ADD_PICTURE_UNKNOWN_NATURAL, null, null);
        }
        
        $natural = $naturals[$i];
        
        //Ověření, zda adresa vede na obrázek (kód inspirovaný odpovědí na StackOverflow: https://stackoverflow.com/a/24936993)
        $typeCheck = false;
        
        $url_headers = @get_headers($url, 1);
        if (isset($url_headers['Content-Type'])){
            $type = @strtolower($url_headers['Content-Type']);
            if (in_array($type, self::ALLOWED_IMAGE_TYPES))
            {
                $typeCheck = true;
            }
        }
        
        if ($typeCheck === false)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ADD_PICTURE_INVALID_FORMAT, null, null);
        }
        
        //Ověření, zda již obrázek u stejné přírodniny není nahrán
        if ($natural->pictureExists($url))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ADD_PICTURE_DUPLICATE_PICTURE, null, null);
        }
        
        return $natural;
    }
    
    /**
     * Metoda vkládající obrázek do databáze a zvyšující počet přidaných obrázků u přihlášeného uživatele
     * @param Natural $natural Objekt přírodniny, ke které chceme přidat obrázek
     * @param string $url Adresa přidávaného obrázku
     * @throws AccessDeniedException V případě, že se obrázek nepodaří přidat
     * @return boolean TRUE, pokud je úspěšně uložen nový obrázek
     */
    private function addPicture(Natural $natural, string $url): bool
    {
        //Vložení obrázku do databáze
        if (!$natural->addPicture($url))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_UNEXPECTED, null, null);
        }
        
        //Zvýšení počtu přidaných obrázků u uživatele
        UserManager::getUser()->incrementAddedPictures();
        
        return true;
    }
}

