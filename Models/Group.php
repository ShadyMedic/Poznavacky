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
     * Konstruktor poznávačky nastavující její ID, jméno, třídu, do které patří a počet jejích částí.
     * Pokud je vše specifikováno, nebude potřeba provádět další SQL dotazy
     * Pokud je vyplněno jméno i ID, ale chybí nějaký z dalších argumentů, má jméno přednost před ID
     * @param int $id ID poztnávačky (nepovinné, pokud je specifikováno jméno)
     * @param string $name Jméno poztnávačky (nepovinné, pokud je specifikováno ID)
     * @param ClassObject $class Objekt třídy, do které poznávačka patří (nepovinné, v případě nevyplnění bude zjištěno z databáze)
     * @param int $partsCount Počet částí, které tato poznávačka obsahuje (nepovinné, v případě nevyplnění bude zjištěno z databáze)
     * @throws AccessDeniedException V případě, že podle ID nebo jména není v databázi nalezena žádná poznávačka
     * @throws BadMethodCallException V případě, že není specifikován dostatek parametrů
     */
    public function __construct(int $id, string $name = "", ClassObject $class = null, int $partsCount = 0)
    {
        if (mb_strlen($name) !== 0 && !empty($id) && !empty($partsCount))
        {
            //Je vše je specifikováno --> nastavit
            $this->id = $id;
            $this->name = $name;
            $this->partsCount = $partsCount;
            $classId = $class->getId();
        }
        else if (mb_strlen($name) !== 0)
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
     * @return ClassObject ID třídy
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
     * Metoda navracející objekt náhodně zvoleného obrázku z nějaké části této poznávačky
     * Šance na výběr části je přímo úměrná počtu přírodnin, které obsahuje
     * Všechny přírodniny této poznávačky tak mají stejnou šanci, že jejich obrázek bude vybrán
     * Počet obrázků u jednotlivých přírodniny nemá na výběr vliv
     * @param int $count Požadovaný počet náhodných obrázků (není zajištěna absence duplikátů)
     */
    public function getRandomPictures(int $count)
    {
        $result = array();
        
        $naturals = $this->getNaturals();
        $naturalsCount = count($naturals);
        for ($i = 0; $i < $count; $i++)
        {
            $randomNaturalNum = rand(0, $naturalsCount - 1);
            $result[] = $naturals[$randomNaturalNum]->getRandomPicture();
        }
        
        return $result;
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
        
        $allPartsIds = array();
        foreach ($this->parts as $part)
        {
            $allPartsIds[] = $part->getId();
        }
        
        $allNaturals = array();
        Db::connect();
        //Problém jak vložit do SQL hodnoty z pole vyřešen podle této odpovědi na StackOverflow: https://stackoverflow.com/a/14767651
        $in = str_repeat('?,', count($allPartsIds) - 1).'?';
        $result = Db::fetchQuery('SELECT prirodniny_id,nazev,obrazky,casti_id FROM prirodniny WHERE casti_id IN ('.$in.')', $allPartsIds, true);
        foreach ($result as $naturalData)
        {
            $part = $this->getPartById($naturalData['casti_id']);
            $allNaturals[] = new Natural($naturalData['prirodniny_id'], $naturalData['nazev'], $this, $part, $naturalData['obrazky']);
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
        $result = Db::fetchQuery('SELECT casti_id,nazev,prirodniny,obrazky FROM casti WHERE poznavacky_id = ?', array($this->id), true);
        foreach ($result as $partData)
        {
            $this->parts[] = new Part($partData['casti_id'], $partData['nazev'], $this, $partData['prirodniny'], $partData['obrazky']);
        }
    }
    
    /**
     * Metoda navracející objekt části této poznávačky, která má specifické ID
     * @param int $id Požadované ID části
     * @return Part Objekt reprezentující část se zadaným ID
     */
    private function getPartById(int $id)
    {
        if (!isset($this->parts))
        {
            $this->loadParts();
        }
        foreach ($this->parts as $part)
        {
            if ($part->getId() === $id)
            {
                return $part;
            }
        }
    }
}