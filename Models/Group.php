<?php
/**
 * Třída reprezentující objekt poznávačky obsahující části
 * @author Jan Štěch
 */
class Group
{
    private $id;
    private $name;
    private $class;
    private $partsCount;
    private $parts;
    
    /**
     * Konstruktor poznávačky nastavující její ID, jméno a třídu, do které patří. Pokud je specifikováno ID i název, má název přednost
     * @param int $id ID poztnávačky (nepovinné, pokud je specifikováno jméno)
     * @param string $name Jméno poztnávačky (nepovinné, pokud je specifikováno ID)
     * @param ClassObject $class Objekt třídy, do které poznávačka patří (nepovinné, v případě nevyplnění bude zjištěno z databáze)
     * @throws BadMethodCallException
     */
    public function __construct(int $id, string $name = "", ClassObject $class = null)
    {
        if (mb_strlen($name) !== 0)
        {
            Db::connect();
            $result = Db::fetchQuery('SELECT poznavacky_id,casti,tridy_id FROM poznavacky WHERE nazev = ? LIMIT 1',array($name));
            if (!$result)
            {
                //Poznávačka nebyla v databázi nalezena
                throw new AccessDeniedException(AccessDeniedException::REASON_GROUP_NOT_FOUND);
            }
            $id = $result['poznavacky_id'];
            $this->partsCount = $result['casti'];
            $classId = $result['tridy_id'];
        }
        else if (!empty($id))
        {
            Db::connect();
            $result = Db::fetchQuery('SELECT nazev,casti,tridy_id FROM poznavacky WHERE poznavacky_id = ? LIMIT 1',array($id));
            if (!$result)
            {
                //Poznávačka nebyla v databázi nalezena
                throw new AccessDeniedException(AccessDeniedException::REASON_GROUP_NOT_FOUND);
            }
            $name = $result['nazev'];
            $this->partsCount = $result['casti'];
            $classId = $result['tridy_id'];
        }
        else
        {
            throw new BadMethodCallException('Either ID or name must be specified.', null, null);
        }
        
        $this->id = $id;
        $this->name = $name;
        
        //Nastavit nebo zjistit třídu
        if (!empty($class) && $class->getId() === $classId)
        {
            //ID souhlasí a objekt je poskytnut --> nastavit
            $this->class = $class;
        }
        else
        {
            //Objekt není poskytnut, nebo nesouhlasí ID --> vytvořit
            $this->class = new ClassObject($classId);
        }
    }
    
    /**
     * Metoda navracející ID této poznávačky
     * @return int ID poznávačky
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Metoda navracející jméno této poztnávačky
     * @return string Jméno poznávačky
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Metoda navracející ID třídy, do které tato poznávačka patří
     * @return int ID třídy
     */
    public function getClass()
    {
        return $this->class;
    }
    
    /**
     * Metoda navracející počet částí v této poznávačce
     * @return int Počet částí poznávačky
     */
    public function getPartsCount()
    {
        return $this->partsCount;
    }
    
    /**
     * Metoda navracející objekty přírodnin ze všech částí této poznávačky
     * Pokud zatím nebyly načteny části této poznávačky, budou načteny z databáze
     * @return Natural[] Pole přírodnin patřících do této poznávačky jako objekty
     */
    public function getNaturals()
    {
        if (!isset($this->parts))
        {
            $this->loadParts();
        }
        
        $allNaturals = array();
        foreach ($this->parts as $part)
        {
            $allNaturals = array_merge($allNaturals, $part->getNaturals());
        }
        return $allNaturals;
    }
    
    /**
     * Metoda navracející část patřící do této poznávačky jako pole objektů
     * @return array Pole částí jako objekty
     */
    public function getParts()
    {
        if (!isset($this->parts))
        {
            $this->loadParts();
        }
        return $this->parts;
    }
    
    /**
     * Metoda načítající části patřící do této poznávačky a ukládající je jako vlastnost
     */
    private function loadParts()
    {
        $this->parts = array();
        
        Db::connect();
        $result = Db::fetchQuery('SELECT casti_id FROM casti WHERE poznavacky_id = ?', array($this->id), true);
        foreach ($result as $partData)
        {
            $this->parts[] = new Part($partData['casti_id'], "", $this);
        }
    }
}