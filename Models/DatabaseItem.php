<?php
/**
 * Abstraktní mateřská třída pro třídy reprezentující záznamy v různých databázových tabulkách
 * @author Jan Štěch
 */
abstract class DatabaseItem
{
    /**
     * Název databázové tabulky, která skladuje záznamy typu této třídy
     * Všechny třídy dědící z této abstraktní třídy musí definovat tuto konstantu
     * @var string
     */
    private const TABLE_NAME = null;
    
    /**
     * Asociativní pole skladující dvojice název vlastnosti objektu a název databázového sloupce, který ji ukládá
     * Všechny třídy dědící z této abstraktní třídy musí definovat tuto konstantu
     * @var array()
     */
    private const COLUMN_DICTIONARY = null;
    
    /**
     * Pole defaultních hodnot, které by byly do databázové tabulky nastaveny v případě jejich nespecifikování v SQL INSERT dotazu
     * Klíče jsou názvy vlastností tohoto objektu, hodnoty jejich defaultní hodnoty
     * Všechny třídy dědící z této abstraktní třídy musí definovat tuto konstantu
     * @var array()
     */
    protected const DEFAULT_VALUES = null;
    
    /**
     * TRUE, pokud se jedná o záznam, který dosud není v databázi uložen
     * V takovém případě nemusí mít objekt při volání funkce save() nastavené ID a nelze na něm zavolat funkci load()
     * @var bool
     */
    protected $savedInDb;
    
    protected $id;
    
    /**
     * Konstruktor položky nastavující její ID nebo informaci o tom, že je nová
     * Pokud se jedná o novou (dosud v databázi neuloženou) položku, jsou do vlastností objektu načteny defaultní hodnoty
     * Tento konstruktor je volán z konstruktorů všech tříd, které z této abstraktní tříd dědí
     * @param bool $isNew FALSE, pokud je již položka se zadaným ID nebo později doplněnými informacemi uložena v databázi, TRUE, pokud se jedná o novou položku
     * @param int $id ID položky (pouze pokud je první argument FALSE)
     */
    public function __construct(bool $isNew, int $id = 0)
    {
        //Nastav všechny vlastnosti na undefined
        $properties = array_keys(get_object_vars($this));
        foreach ($properties as $property)
        {
            $this->$property = new undefined();
        }
        
        if ($isNew)
        {
            //Nová položka bez známých informací
            $this->savedInDb = false;
            $this->loadDefaultValues();
        }
        else if (!empty($id))
        {
            //Položka uložená v databázi se známým ID
            $this->id = $id;
            $this->savedInDb = true;
        }
        else
        {
            //Položka uložená v databázi s neznámým ID, ale známými jinými informacemi, které jsou později doplněny skrze metodu initialize()
            $this->savedInDb = true;
        }
    }
    
    /**
     * Metoda nastavující všechny vlastnosti objektu podle proměnných poskytnutých v argumentech
     * V případě nespecifikování všech argumentů jsou neznámé vlastnosti naplněny základními hodnotami
     */
    public abstract function initialize();
    
    /**
     * Metoda navracející ID tohoto databázového záznamu
     * @return int ID záznamu
     */
    public function getId()
    {
        $this->loadIfNotLoaded($this->loaded);
        return $this->id;
    }
    
    /**
     * Metoda zjišťující, zda je daná proměnná definována (zda je do ní přiřazeno cokoliv jiného než objekt typu undefined
     * @param mixed $property
     * @return boolean TRUE, pokud proměnná obsahuje cokoliv jiného než objekt typu undefined (včetně null)
     */
    public function isDefined($property)
    {
        return (!$property instanceof undefined);
    }
    
    /**
     * Metoda načítající všechny vlastnosti objektu z databáze, pokud jakákoliv z vlastností objektů není definována
     */
    protected function loadIfNotAllLoaded()
    {
        //Kontrola, zda není nějaká vlastnost nedefinována
        $properties = get_object_vars($this);
        foreach ($properties as $property)
        {
            if (!$this->isDefined($property))
            {
                $this->load();
                return;
            }
        }
    }
    
    /**
     * Metoda načítající všechny vlastnosti objektu z databáze, pokud vlastnost specifikovaná jako argument není definována
     */
    protected function loadIfNotLoaded($property)
    {
        if (!$this->isDefined($property))
        {
            $this->load();
        }
    }
    
    /**
     * Metoda nastavující do vlastností objektu základní hodnoty, které by byly uloženy do databáze i v případě jejich nespecifikování v SQL INSERT dotazu
     * @param bool $overwriteAll TRUE, pokud mají být základními hodnotami přepsány všechny vlastnosti objektu, FALSE pouze pro přepsání vlastností, jejichž hodnota není nastavena nebo je nastavena na NULL
     */
    protected function loadDefaultValues(bool $overwriteAll = false)
    {
        foreach ($this::DEFAULT_VALUES as $fieldName => $fieldValue)
        {
            if (!$overwriteAll)
            {
                if ($this->$fieldName !== null){ continue; }
            }
            $this->$fieldName = $fieldValue;
        }
    }
    
    /**
     * Metoda prověřující všechny vlastnosti objektu na jejich definovanost a navracející pole se jmény nedefinovaných vlastností
     * Jako nedefinovaná vlastnost se rozumí vlastnost, která ukládá instanci třídy undefined, vlastnost ukládající hodnotu NULL je definovaná
     * @return string[] Pole obsahující názvy vlastností, které nejsou definované jako klíče a instance třídy undefined jako hodnoty
     */
    protected function getUndefinedProperties()
    {
        //Ukládání nedefinovaných vlastností objektu
        return $this->getPropertyList(false);
    }
    
    /**
     * Metoda prověřující všechny vlastnosti objektu na jejich definovanost a navracející pole se jmény definovaných vlastností
     * Jako definovaná vlastnost se rozumí vlastnost, která ukládá cokoliv jiného než instanci třídy undefined, vlastnost ukládající hodnotu NULL je definovaná
     * @return string[] Pole obsahující názvy vlastností, které jsou definované jako klíče a jejich hodnoty jako hodnoty
     */
    protected function getDefinedProperties()
    {
        //Ukládání definovaných vlastností objektu
        return $this->getPropertyList(true);
    }
    
    /**
     * Metoda získávající buďto pole názvů definovaných vlastností objektu, nebo pole názvů nedefinovaných vlastností objektu
     * @param bool $getDefined TRUE, pokud má být navrácen seznam názvů definovaných vlastností, FALSE, pokud nedefinovaných
     * @return array Pole obsahující názvy definovaných nebo nedefinovaných vlastností objektu jako klíče a jejich hodnoty jako hodnoty
     */
    private function getPropertyList(bool $getDefined)
    {
        $result = array();
        $properties = get_object_vars($this);
        foreach ($properties as $propertyName => $propertyValue)
        {
            if ($getDefined == $this->isDefined($propertyValue))
            {
                $result[$propertyName] = $propertyValue;
            }
        }
        return $result;
    }
    
    /**
     * Metoda načítající podle údajů poskytnutých v konstruktoru a metodě initialize všechny ostatní vlastnosti objektu
     * @param bool $rewriteKnown TRUE, pokud mají být načtenými hodnotami přepsány vlastnosti obsahující i něco jiného než instanci třídy undefined
     */
    public abstract function load(bool $rewriteKnown = false);
    
    /**
     * Metoda ukládající všechny definované vlastnosti objektu do databáze, přepisujíce záznam se stejným ID nebo vytvářející nový v případě, že záznam s takovým ID v databázové tabulce neexistuje
     * Neznámé vlastnosti (obsahující instanci undefined) nejsou ukládány
     */
    public abstract function save();
    
    /**
     * Metoda vytvářející v databázové tabulce nový záznam s daty dané položky
     * I pokud je vyplněno ID nebo je vlastnost $savedInDb nastavena na TRUE, bude položka uložena jako nový záznam a vlastnost ID objektu bude přepsána
     * @throws BadMethodCallException Pokud některá z vlastností ukládaných do databáze není známa
     * @return boolean TRUE, pokud je úspěšně vytvořen v databázi nový záznam a ID položky nastaveno / aktualizováno, FALSE, pokud ne
     */
    protected function create()
    {
        //Zkontroluj, zda jsou všechny potřebné vlastnosti vyplněny a sestav pole hodnot pro vložení a názvů databázových sloupců
        $databaseColumnNames = array();
        $databaseColumnValues = array();
        
        $databaseProperties = $this::COLUMN_DICTIONARY;
        unset($databaseProperties['id']);   //Odebrat ze seznamu vlastnost ID (nemůže být nastaveno pro nový záznam)
        foreach ($databaseProperties as $propertyName => $columnName)
        {
            $propertyValue = $this->$propertyName;
            if (!$this->isDefined($propertyValue))
            {
                throw new BadMethodCallException('Values of all database columns must be know before creating a new record in the table');
            }
            $databaseColumnNames[] = $columnName;
            if ($propertyValue instanceof DatabaseItem) { $databaseColumnValues[] = $propertyValue->getId(); }  //Pro případ, že vlastnost ukládá odkaz na objekt
            else { $databaseColumnValues[] = $propertyValue; }
        }
        
        //Sestav řetězce pro vložení do SQL dotazu
        $columnString = implode(',', $databaseColumnNames);
        $valuesString = str_repeat('?,', count($databaseColumnNames) - 1).'?';
        
        //Proveď SQL dotaz
        $this->id = Db::executeQuery('INSERT INTO '.$this::TABLE_NAME.' ('.$columnString.') VALUES ('.$valuesString.')', $databaseColumnValues, true);
        if (!empty($this->id))
        {
            $this->savedInDb = true;
            return true;
        }
        return false;
    }
    
    /**
     * Metoda ukládající data této databázové položky do databáze
     * Tato metoda NEVYTVÁŘÍ nový záznam v databázy, pouze aktualizuje již existující
     * ID položky musí být známo a data záznamu se stejným ID jsou v databázi přepsána
     * @throws BadMethodCallException Pokud není známo ID záznamu
     * @return boolean TRUE, pokud je položka úspěšně v databázi aktualizována, FALSE, pokud nejsou známy žádné vlastnosti nebo pokud selže SQL dotaz
     */
    protected function update()
    {
        //Zkontrolovat, zda je známé ID
        if (!$this->isDefined($this->id))
        {
            throw new BadMethodCallException('ID of the item must be loaded before saving into the database, since this item isn\'t new');
        }
        
        //Získat seznam definovaných vlastností (včetně jejich hodnot)
        $definedProperties = $this->getDefinedProperties();
        
        //Odebrat ze seznamu ID záznamu (nemůže být změněno)
        unset($definedProperties['id']);
        
        //Sestavit řetězec databázových sloupců a jejich hodnot pro SQL dotaz
        $databaseColumnNames = array();
        $databaseColumnValues = array();
        foreach ($definedProperties as $propertyName => $propertyValue)
        {
            if (isset($this::COLUMN_DICTIONARY[$propertyName]))    //Aby se ukládali pouze vlastnosti propojené s databázovým sloupcem
            {
                $databaseColumnNames[] = $this::COLUMN_DICTIONARY[$propertyName];
                if ($propertyValue instanceof DatabaseItem) { $databaseColumnValues[] = $propertyValue->getId(); }  //Pro případ, že vlastnost ukládá odkaz na objekt
                else { $databaseColumnValues[] = $propertyValue; }
            }
        }
        
        if (count($databaseColumnNames) === 0) { return false; }
        $columnString = implode(' = ?,', $databaseColumnNames);
        $columnString .= ' = ?'; //Přidání rovnítka s otazníkem za název posledního sloupce
        
        Db::connect();
        return Db::executeQuery('UPDATE '.$this::TABLE_NAME.' SET '.$columnString.' WHERE '.$this::COLUMN_DICTIONARY['id'].' = ? LIMIT 1', array_merge(array_values($definedProperties), array_pad($databaseColumnValues, count($databaseColumnValues) + 1, $this->id)));
    }
    
    /**
     * Metoda odstraňující záznam reprezentovaný tímto objektem z databáze a nulující vlastnost ID objektu
     */
    public abstract function delete();
}