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
    
    protected const DEFAULT_VALUES = array(
        'partsCount' => 0,
        'parts' => array()
    );
    
    protected $name;
    protected $class;
    protected $partsCount;
    
    protected $parts;
    
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
        //Načtení defaultních hodnot do nenastavených vlastností
        $this->loadDefaultValues();
        
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
     * Metoda načítající z databáze všechny vlastnosti objektu s výjimkou seznamu částí, do kterých je tato poznávačka rozdělena podle ID (pokud je vyplněno)
     * Pokud není známé ID této poznávačky, ale je známa třída, do které tato poznávačka patří a název poznávačky, jsou ostatní informace (včetně ID a s výjimkou seznamu částí, do kterých je tato poznávačka rozdělena) načteny podle těchto informací
     * Seznam částí, do kterých je tato poznávačka rozdělena může být načten do vlastnosti Group::$parts pomocí metody Group::loadParts()
     * @throws BadMethodCallException Pokud se jedná o poznávačku, která dosud není uložena v databázi nebo pokud není o objektu známo dost informací potřebných pro jeho načtení
     * @throws NoDataException Pokud není poznávačka s odpovídajícími daty nalezena v databázi
     * @return boolean TRUE, pokud jsou vlastnosti této poznávačky úspěšně načteny z databáze
     * {@inheritDoc}
     * @see DatabaseItem::load()
     */
    public function load()
    {
        if ($this->savedInDb === false)
        {
            throw new BadMethodCallException('Cannot load data about an item that is\'t saved in the database yet');
        }
        
        Db::connect();
        
        if ($this->isDefined($this->id))
        {
            $result = Db::fetchQuery('SELECT '.Group::COLUMN_DICTIONARY['name'].', '.Group::COLUMN_DICTIONARY['class'].', '.Group::COLUMN_DICTIONARY['partsCount'].' FROM '.self::TABLE_NAME.' WHERE '.Group::COLUMN_DICTIONARY['id'].' = ? LIMIT 1', array($this->id));
            if (empty($result))
            {
                throw new NoDataException(NoDataException::UNKNOWN_GROUP);
            }
            
            $name = $result[Group::COLUMN_DICTIONARY['name']];
            $class = new ClassObject(false, $result[Group::COLUMN_DICTIONARY['class']]);
            $partsCount = $result[Group::COLUMN_DICTIONARY['partsCount']];
            $this->initialize($name, $class, null, $partsCount);
        }
        else if ($this->isDefined($this->name) && $this->isDefined($this->class))
        {
            $result = Db::fetchQuery('SELECT '.Group::COLUMN_DICTIONARY['id'].', '.Group::COLUMN_DICTIONARY['partsCount'].' FROM '.self::TABLE_NAME.' WHERE '.Group::COLUMN_DICTIONARY['name'].' = ? AND '.Group::COLUMN_DICTIONARY['class'].' = ? LIMIT 1', array($this->name, $this->class->getId()));
            if (empty($result))
            {
                throw new NoDataException(NoDataException::UNKNOWN_GROUP);
            }
            
            $this->id = $result[Group::COLUMN_DICTIONARY['id']];
            $partsCount = $result[Group::COLUMN_DICTIONARY['partsCount']];
            $this->initialize(null, null, null, $partsCount);
        }
        else
        {
            throw new BadMethodCallException('Not enough properties are know about the item to be able to load the rest');
        }
        
        return true;
    }
    
    /**
     * Metoda ukládající data této poznávačky do databáze
     * Pokud se jedná o novou poznávačku (vlastnost $savedInDb je nastavena na FALSE), je vložen nový záznam
     * V opačném případě jsou přepsána data poznávačky se stejným ID
     * @throws BadMethodCallException Pokud se nejedná o novou poznávačku a zároveň není známo jeho ID (znalost ID poznávačky je nutná pro modifikaci databázové tabulky)
     * @return boolean TRUE, pokud je poznávačka úspěšně uložena do databáze
     * {@inheritDoc}
     * @see DatabaseItem::save()
     */
    public function save()
    {
        if ($this->savedInDb === true && !$this->isDefined($this->id))
        {
            throw new BadMethodCallException('ID of the item must be loaded before saving into the database, since this item isn\'t new');
        }
        
        Db::connect();
        if ($this->savedInDb)
        {
            //Aktualizace existující poznávačky
            $this->loadIfNotAllLoaded();
            
            $result = Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET '.Group::COLUMN_DICTIONARY['name'].' = ?, '.Group::COLUMN_DICTIONARY['class'].' = ?, '.Group::COLUMN_DICTIONARY['partsCount'].' = ? WHERE '.Group::COLUMN_DICTIONARY['id'].' = ? LIMIT 1', array($this->name, $this->class->getId(), $this->partsCount, $this->id));
        }
        else
        {
            //Tvorba nové poznávačky
            $this->id = Db::executeQuery('INSERT INTO '.self::TABLE_NAME.' ('.Group::COLUMN_DICTIONARY['name'].','.Group::COLUMN_DICTIONARY['class'].','.Group::COLUMN_DICTIONARY['partsCount'].') VALUES (?,?,?)', array($this->name, $this->class->getId(), $this->partsCount), true);
            if (!empty($this->id))
            {
                $this->savedInDb = true;
                $result = true;
            }
        }
        return $result;
    }
    
    /**
     * Metoda navracející ID této poznávačky
     * @return int ID poznávačky
     */
    public function getId()
    {
        $this->loadIfNotLoaded($this->id);
        return $this->id;
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
        if (!$this->isDefined($this->parts)){ $this->loadParts(); }
        
        $allPartsIds = array();
        foreach ($this->parts as $part)
        {
            $allPartsIds[] = $part->getId();
        }
        
        $allNaturals = array();
        Db::connect();
        //Problém jak vložit do SQL hodnoty z pole vyřešen podle této odpovědi na StackOverflow: https://stackoverflow.com/a/14767651
        $in = str_repeat('?,', count($allPartsIds) - 1).'?';
        $result = Db::fetchQuery('SELECT '.Natural::COLUMN_DICTIONARY['id'].','.Natural::COLUMN_DICTIONARY['name'].','.Natural::COLUMN_DICTIONARY['picturesCount'].','.Natural::COLUMN_DICTIONARY['part'].' FROM '.Natural::TABLE_NAME.' WHERE '.Natural::COLUMN_DICTIONARY['part'].' IN ('.$in.')', $allPartsIds, true);
        foreach ($result as $naturalData)
        {
            $part = $this->getPartById($naturalData[Natural::COLUMN_DICTIONARY['part']]);
            $natural = new Natural(false, $naturalData[Natural::COLUMN_DICTIONARY['id']]);
            $natural->initialize($naturalData[Natural::COLUMN_DICTIONARY['name']], null, $naturalData[Natural::COLUMN_DICTIONARY['picturesCount']], null, $this, $part);
            $allNaturals[] = $natural;
        }
        return $allNaturals;
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
        
        Db::connect();
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
        Db::connect();
        $result = Db::fetchQuery('
            SELECT
            '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['id'].' AS "hlaseni_id", '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['reason'].' AS "hlaseni_duvod", '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['additionalInformation'].' AS "hlaseni_dalsi_informace", '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['reportersCount'].' AS "hlaseni_pocet",
            '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['id'].' AS "obrazky_id", '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['src'].' AS "obrazky_zdroj", '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['enabled'].' AS "obrazky_povoleno",
            '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['id'].' AS "prirodniny_id", '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['name'].' AS "prirodniny_nazev", '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['picturesCount'].' AS "prirodniny_obrazky", '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['part'].' AS "prirodniny_cast"
            FROM hlaseni
            JOIN '.Picture::TABLE_NAME.' ON '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['picture'].' = '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['id'].'
            JOIN '.Natural::TABLE_NAME.' ON '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['natural'].' = '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['id'].'
            WHERE '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['reason'].' IN ('.$in.')
            AND '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['group'].' = ?;
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
            $natural->initialize($reportInfo['prirodniny_nazev'], null, $reportInfo['prirodniny_obrazky'], null, $this, new Part(false, $reportInfo['prirodniny_cast']));
            $picture = new Picture(false, $reportInfo['obrazky_id']);
            $picture->initialize($reportInfo['obrazky_zdroj'], $natural, null, $reportInfo['obrazky_povoleno'], $natural->getPart());
            $report = new Report(false, $reportInfo['hlaseni_id']);
            $report->initialize($picture, $reportInfo['hlaseni_duvod'], $reportInfo['hlaseni_dalsi_informace'], $reportInfo['hlaseni_pocet']);
            $reports[] = $report;
        }
        
        return $reports;
    }
    
    /**
     * Metoda odstraňující tuto poznávačku z databáze
     * @return boolean TRUE, pokud je poznávačka úspěšně odstraněna z databáze
     * {@inheritDoc}
     * @see DatabaseItem::delete()
     */
    public function delete()
    {
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        Db::executeQuery('DELETE FROM '.self::TABLE_NAME.' WHERE '.Group::COLUMN_DICTIONARY['id'].' = ? LIMIT 1;', array($this->id));
        $this->id = new undefined();
        $this->savedInDb = false;
        return true;
    }
}