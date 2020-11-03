<?php
/**
 * Třída reprezentující objekt poznávačky obsahující části
 * @author Jan Štěch
 */
class Group extends DatabaseItem
{
    public const TABLE_NAME = 'poznavacky';
    
    public const COLUMN_DICTIONARY = array(
        'id' => 'poznavacky_id',
        'name' => 'nazev',
        'class' => 'tridy_id',
        'partsCount' => 'casti'
    );
    
    protected const NON_PRIMITIVE_PROPERTIES = array(
        'class' => ClassObject::class
    );
    
    protected const DEFAULT_VALUES = array(
        'partsCount' => 0,
        'parts' => array()
    );
    
    protected const CAN_BE_CREATED = true;
    protected const CAN_BE_UPDATED = true;
    
    protected $name;
    protected $class;
    protected $partsCount;
    
    protected $parts;
    protected $naturals;
    
    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * Při nastavení některého z argumentů na undefined, je hodnota dané vlastnosti také nastavena na undefined
     * Při nastavení některého z argumentů na null, není hodnota dané vlastnosti nijak pozměněna
     * @param string|undefined|null $name Název této poznávačky
     * @param ClassObject|undefined|null $class Odkaz na objekt třídy, do které tato poznávačka patří
     * @param Part[]|undefined|null $parts Pole částí, jako objekty, na které je tato poznávačka rozdělená
     * @param int|undefined|null Počet částí, do kterých je tato poznávačka rozdělena (při vyplnění parametru $parts je ignorováno a je použita délka poskytnutého pole)
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($name = null, $class = null, $parts = null, $partsCount = null)
    {
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($name === null){ $name = $this->name; }
        if ($class === null){ $class = $this->class; }
        if ($parts === null)
        {
            $parts = $this->parts;
            if ($partsCount === null){ $partsCount = $this->partsCount; }
        }
        else { $partsCount = count($parts); }
        
        $this->name = $name;
        $this->class = $class;
        $this->parts = $parts;
        $this->partsCount = $partsCount;
    }
    
    /**
     * Metoda navracející jméno této poztnávačky
     * @return string Jméno poznávačky
     */
    public function getName()
    {
        $this->loadIfNotLoaded($this->name);
        return $this->name;
    }
    
    /**
     * Metoda navracející ID třídy, do které tato poznávačka patří
     * @return ClassObject ID třídy
     */
    public function getClass()
    {
        $this->loadIfNotLoaded($this->class);
        return $this->class;
    }
    
    /**
     * Metoda navracející počet částí v této poznávačce
     * @return int Počet částí poznávačky
     */
    public function getPartsCount()
    {
        $this->loadIfNotLoaded($this->partsCount);
        return $this->partsCount;
    }
    
    /**
     * Metoda navracející pole náhodně zvolených obrázků z nějaké části této poznávačky jako objekty
     * Šance na výběr části je přímo úměrná počtu přírodnin, které obsahuje
     * Všechny přírodniny této poznávačky tak mají stejnou šanci, že jejich obrázek bude vybrán
     * Počet obrázků u jednotlivých přírodniny nemá na výběr vliv
     * @param int $count Požadovaný počet náhodných obrázků (není zajištěna absence duplikátů)
     * @return Picture[] Polé náhodně vybraných obrázků obsahující specifikovaný počet prvků
     */
    public function getRandomPictures(int $count)
    {
        $result = array();
        
        $naturals = $this->getNaturals();
        $naturalsCount = count($naturals);
        for ($i = 0; $i < $count; $i++)
        {
            $randomNaturalNum = rand(0, $naturalsCount - 1);
            $picture = $naturals[$randomNaturalNum]->getRandomPicture();
            if ($picture === null)  //Kontrola, zda byl u vybrané přírodniny alespoň jeden obrázek
            {
                $i--;
                continue;
            }
            $result[] = $picture;
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
        if (!$this->isDefined($this->naturals))
        {
            $this->loadNaturals();
        }
        return $this->naturals;
    }
    
    /**
     * Metoda načítající seznam přírodnin patřících do této poznávačky a ukládající jejich instance do vlastnosti $naturals jako pole
     */
    private function loadNaturals()
    {
        $this->loadIfNotLoaded($this->id);
        $this->loadIfNotLoaded($this->class);
        
        $allNaturals = array();
        $result = Db::fetchQuery('
            SELECT '.Natural::COLUMN_DICTIONARY['id'].','.Natural::COLUMN_DICTIONARY['name'].','.Natural::COLUMN_DICTIONARY['picturesCount'].'
            FROM '.Natural::TABLE_NAME.'
            WHERE '.Natural::COLUMN_DICTIONARY['id'].' IN (
                SELECT prirodniny_id FROM prirodniny_casti
                WHERE casti_id IN (
                    SELECT '.Part::COLUMN_DICTIONARY['id'].' FROM '.Part::TABLE_NAME.'
                    WHERE '.Part::COLUMN_DICTIONARY['group'].' = ?
                )
            );
        ', array($this->id), true);
        foreach ($result as $naturalData)
        {
            $natural = new Natural(false, $naturalData[Natural::COLUMN_DICTIONARY['id']]);
            $natural->initialize($naturalData[Natural::COLUMN_DICTIONARY['name']], null, $naturalData[Natural::COLUMN_DICTIONARY['picturesCount']], $this->class);
            $allNaturals[] = $natural;
        }
        $this->naturals = $allNaturals;
    }
    
    /**
     * Metoda zjišťující, zda se přírodnina s daným ID vyskytuje v této poznávačce
     * @param Natural $natural Objekt přírodniny pro ověření (mělo by být vyplněné její ID)
     * @return boolean TRUE, pokud se poskytnutá přírodnina nachází v této poznávačce, FALSE, pokud ne
     */
    public function containsNatural(Natural $natural)
    {
        foreach ($this->getNaturals() as $presentNatural)
        {
            if ($presentNatural->getId() === $natural->getId()) { return true; }
        }
        return false;
    }
    
    /**
     * Metoda navracející část patřící do této poznávačky jako pole objektů
     * @return array Pole částí jako objekty
     */
    public function getParts()
    {
        if (!$this->isDefined($this->parts))
        {
            $this->loadParts();
        }
        return $this->parts;
    }
    
    /**
     * Metoda načítající části patřící do této poznávačky a ukládající je jako vlastnost
     */
    public function loadParts()
    {
        $this->loadIfNotLoaded($this->id);
        
        $result = Db::fetchQuery('SELECT '.Part::COLUMN_DICTIONARY['id'].','.Part::COLUMN_DICTIONARY['name'].','.Part::COLUMN_DICTIONARY['naturalsCount'].','.Part::COLUMN_DICTIONARY['picturesCount'].' FROM '.Part::TABLE_NAME.' WHERE '.Part::COLUMN_DICTIONARY['group'].' = ?', array($this->id), true);
        if ($result === false || count($result) === 0)
        {
            //Žádné části nenalezeny
            $this->parts = array();
        }
        else
        {
            $this->parts = array();
            foreach ($result as $partData)
            {
                $part = new Part(false, $partData[Part::COLUMN_DICTIONARY['id']]);
                $part->initialize($partData[Part::COLUMN_DICTIONARY['name']], $this, null, $partData[Part::COLUMN_DICTIONARY['naturalsCount']], $partData[Part::COLUMN_DICTIONARY['picturesCount']]);
                $this->parts[] = $part;
            }
        }
    }
    
    /**
     * Metoda navracející objekt části této poznávačky, která má specifické ID
     * @param int $id Požadované ID části
     * @return Part Objekt reprezentující část se zadaným ID
     */
    private function getPartById(int $id)
    {
        if (!$this->isDefined($this->parts)){ $this->loadParts(); }
        foreach ($this->parts as $part)
        {
            if ($part->getId() === $id)
            {
                return $part;
            }
        }
    }
    
    public function getReports()
    {
        $this->loadIfNotLoaded($this->id);
        
        //Získání důvodů hlášení vyřizovaných správcem třídy
        $availableReasons = array_diff(Report::ALL_REASONS, Report::ADMIN_REQUIRING_REASONS);
        
        $in = str_repeat('?,', count($availableReasons) - 1).'?';
        $sqlArguments = array_values($availableReasons);
        $sqlArguments[] = $this->id;
        $result = Db::fetchQuery('
            SELECT
            '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['id'].' AS "hlaseni_id", '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['reason'].' AS "hlaseni_duvod", '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['additionalInformation'].' AS "hlaseni_dalsi_informace", '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['reportersCount'].' AS "hlaseni_pocet",
            '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['id'].' AS "obrazky_id", '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['src'].' AS "obrazky_zdroj", '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['enabled'].' AS "obrazky_povoleno",
            '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['id'].' AS "prirodniny_id", '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['name'].' AS "prirodniny_nazev", '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['picturesCount'].' AS "prirodniny_obrazky" 
            FROM hlaseni
            JOIN '.Picture::TABLE_NAME.' ON '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['picture'].' = '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['id'].'
            JOIN '.Natural::TABLE_NAME.' ON '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['natural'].' = '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['id'].'
            WHERE '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['reason'].' IN ('.$in.')
            AND '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['id'].' IN (
                SELECT prirodniny_id 
                FROM prirodniny_casti 
                WHERE casti_id IN (
                    SELECT '.Part::COLUMN_DICTIONARY['id'].' 
                    FROM '.Part::TABLE_NAME.' 
                    WHERE '.Part::COLUMN_DICTIONARY['group'].' = ?
                )
            );
        ', $sqlArguments, true);
        
        if ($result === false)
        {
            //Žádná hlášení nenalezena
            return array();
        }
        
        $reports = array();
        foreach ($result as $reportInfo)
        {
            $natural = new Natural(false, $reportInfo['prirodniny_id']);
            $natural->initialize($reportInfo['prirodniny_nazev'], null, $reportInfo['prirodniny_obrazky'], null);
            $picture = new Picture(false, $reportInfo['obrazky_id']);
            $picture->initialize($reportInfo['obrazky_zdroj'], $natural, $reportInfo['obrazky_povoleno'], null);
            $report = new Report(false, $reportInfo['hlaseni_id']);
            $report->initialize($picture, $reportInfo['hlaseni_duvod'], $reportInfo['hlaseni_dalsi_informace'], $reportInfo['hlaseni_pocet']);
            $reports[] = $report;
        }
        
        return $reports;
    }
}