<?php
namespace Poznavacky\Models\Processors;

use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\DatabaseItems\Folder;
use Poznavacky\Models\DatabaseItems\Group;
use Poznavacky\Models\DatabaseItems\Part;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Security\DataValidator;
use \InvalidArgumentException;
use \RangeException;

/**
 * Třída starající se o zpracování dat odeslaných z formuláře pro přidání poznávačky do třídy a případně vytvářející novou poznávačku
 * @author Jan Štěch
 */
class GroupAdder
{
    private const DEFAULT_PART_NAME = "Hlavní část";

    private $class;

    /**
     * Konstruktor nastavující objekt třídy, do které bude objekt přidávat poznávačky
     * @param ClassObject $class Objekt třídy
     */
    public function __construct(ClassObject $class)
    {
        $this->class = $class;
    }

    /**
     * Metoda zpracovávající data odeslaná z formuláře na stránce se správou třídy
     * Data jsou ověřena a posléze i uložena do databáze, nebo je vyvolána výjimka s chybovou hláškou
     * @param array $POSTdata Pole dat odeslaných z formuláře
     * @return Group Objekt nové poznávačky, která je již uložena do databáze
     */
    public function processFormData(array $POSTdata): Group
    {
        $groupName = $POSTdata['testName'];

        $this->checkData($groupName);       //Kontrola dat
        return $this->addGroup($groupName); //Ovlivnění databáze
    }

    /**
     * Metoda ověřující, zda jsou poskytnutá data v pořádku
     * @param string $groupName Název přidávané poznávačky
     * @throws AccessDeniedException V případě že data nesplňují podmínky
     * @return boolean TRUE, pokud může být daný název použit
     */
    public function checkData(string $groupName): bool
    {
        //Kontrola, zda již poznávačka s tímto URL ve třídě neexistuje
        $validator = new DataValidator();
        $url = Folder::generateUrl($groupName);
        try
        {
            $validator->checkUniqueness($url, DataValidator::TYPE_GROUP_URL, $this->class);
        }
        catch (InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NEW_GROUP_DUPLICATE_NAME, null, $e);
        }

        //Kontrola, zda URL poznávačky není rezervované pro žádný kontroler
        try
        {
            $validator->checkForbiddenUrls($url, DataValidator::TYPE_GROUP_URL);
        }
        catch (InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NEW_GROUP_FORBIDDEN_URL, null, $e);
        }

        //Kontrola, zda není název příliš krátký nebo dlouhý nebo neobsahuje nepovolené znaky
        try
        {
            $validator->checkLength($groupName, DataValidator::GROUP_NAME_MIN_LENGTH, DataValidator::GROUP_NAME_MAX_LENGTH, DataValidator::TYPE_GROUP_NAME);
            $validator->checkCharacters($groupName, DataValidator::GROUP_NAME_ALLOWED_CHARS, DataValidator::TYPE_GROUP_NAME);
        }
        catch(RangeException $e)
        {
            if ($e->getMessage() === 'long'){throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NEW_GROUP_NAME_TOO_LONG, null, $e);}
            else if ($e->getMessage() === 'short'){throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NEW_GROUP_NAME_TOO_SHORT, null, $e);}
        }
        catch(InvalidArgumentException $e){throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NEW_GROUP_NAME_INVALID_CHARACTERS, null, $e);}
        
        return true;
    }

    /**
     * Metoda vkládající poznávačku do databáze a přidávající do ní první, prázdnou část
     * @param string $groupName Název pro novou poznávačku (musí být ověřen metodou GroupAdder::checkData())
     * @throws AccessDeniedException V případě, že se poznávačku nepodaří vytvořit
     * @return Group Objekt nově přidané poznávačky
     */
    private function addGroup(string $groupName): Group
    {
        //Vložení poznávačky do databáze
        $newGroup = $this->class->addGroup($groupName);
        if ($newGroup === false)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_UNEXPECTED, null, null);
        }

        //Vytvoření první části poznávačky
        $part = new Part(true);
        $part->initialize(self::DEFAULT_PART_NAME, Folder::generateUrl(self::DEFAULT_PART_NAME), $newGroup, array(), 0, 0);
        $newGroup->replaceParts(array($part));
        return $newGroup;
    }
}

