<?php
/**
 * Třída starající se o zpracování dat odeslaných z formuláře pro přidání poznávačky do třídy a případně vytvářející novou poznávačku
 * @author Jan Štěch
 */
class GroupAdder
{
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
     * @return boolean TRUE, pokud vše proběhne tak, jak má
     */
    public function processFormData(array $POSTdata)
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
    public function checkData(string $groupName)
    {
        //Kontrola, zda již poznávačka s tímto názvem ve třídě neexistuje
        if ($this->class->groupExists($groupName))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NEW_GROUP_DUPLICATE_NAME);
        }
        
        $validator = new DataValidator();
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
     * Metoda vkládající poznávačku do databáze
     * @param string $groupName Název pro novou poznávačku (musí být ověřen metodou GroupAdder::checkData())
     * @throws AccessDeniedException V případě, že se poznávačku nepodaří vytvořit
     * @return boolean TRUE, pokud je úspěšně uložen nový obrázek
     */
    private function addGroup(string $groupName)
    {
        //Vložení poznávačky do databáze
        if (!$this->class->addGroup($groupName))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_UNEXPECTED, null, null);
        }
        
        return true;
    }
}