<?php
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
    
    private $class;
    private $group;
    
    /**
     * Konstruktor třídy nastavující objekt třídy a poznávačky, do které bude tato třída přidávat obrázky
     * @param ClassObject $class Objekt třídy
     * @param Group $group Objekt poznávačky (musí patřit do třídy)
     */
    public function __construct(ClassObject $class, Group $group)
    {
        $this->classObject = $class;
        $this->group = $group;
    }
    
    /**
     * Metoda zpracovávající data odeslaná z formuláře na stránce pro přidávání obrázků
     * Data jsou ověřena a posléze i uložena do databáze, nebo je vyvolána výjimka s chybovou hláškou
     * @param array $POSTdata Pole dat odeslaných z formuláře
     * @throws AccessDeniedException V případě že data nesplňují podmínky
     * @return boolean TRUE, pokud je úspěšně uložen nový obrázek
     */
    public function processFormData(array $POSTdata)
    {
        $naturalName = $POSTdata['naturalName'];
        $url = $POSTdata['url'];
        
        $naturals = $this->group->getNaturals();
        for ($i = 0; $i < count($naturals) && $naturals[$i]->getName() !== $naturalName; $i++){}
        
        //Přírodnina s tímto názvem ve zvolené poznávačce neexistuje
        if ($i === count($naturals))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ADD_PICTURE_UNKNOWN_NATURAL, null, null, array('originFile' => 'PictureAdder.php', 'displayOnView' => 'addPictures.phtml'));
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
            throw new AccessDeniedException(AccessDeniedException::REASON_ADD_PICTURE_INVALID_FORMAT, null, null, array('originFile' => 'PictureAdder.php', 'displayOnView' => 'addPictures.phtml'));
        }
        
        //Ověření, zda již obrázek u stejné přírodniny není nahrán
        if ($natural->pictureExists($url))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ADD_PICTURE_DUPLICATE_PICTURE, null, null, array('originFile' => 'PictureAdder.php', 'displayOnView' => 'addPictures.phtml'));
        }
        
        //Vložení obrázku do databáze
        if (!$natural->addPicture($url))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_UNEXPECTED, null, null, array('originFile' => 'PictureAdder.php', 'displayOnView' => 'addPictures.phtml'));
        }
        
        //Zvýšení počtu přidaných obrázků u uživatele
        UserManager::getUser()->incrementAddedPictures();
        
        return true;
    }
}