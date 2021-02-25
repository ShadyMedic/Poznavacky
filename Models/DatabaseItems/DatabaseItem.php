<?php
namespace Poznavacky\Models\DatabaseItems;

use Poznavacky\Models\Security\AntiXssSanitizer;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\undefined;
use \BadMethodCallException;
use \DateTime;
use \ReflectionClass;
use \UnexpectedValueException;

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
     * Asociativní pole skladující dvojice název vlastnosti objektu, které ukládá objekt (například instanci třídy User nebo ClassObject)
     * Všechny třídy dědící z této abstraktní třídy musejí definovat tuto konstantu
     * @var array()
     */
    private const NON_PRIMITIVE_PROPERTIES = null;
    
    /**
     * Pole defaultních hodnot, které by byly do databázové tabulky nastaveny v případě jejich nespecifikování v SQL INSERT dotazu
     * Klíče jsou názvy vlastností tohoto objektu, hodnoty jejich defaultní hodnoty
     * Všechny třídy dědící z této abstraktní třídy musí definovat tuto konstantu
     * @var array()
     */
    protected const DEFAULT_VALUES = null;
    
    /**
     * TRUE, pokud mohou být do databáze ukládány nové záznamy této položky, FALSE, pokud ne
     * @var bool
     */
    protected const CAN_BE_CREATED = true;
    
    /**
     * TRUE, pokud mohou být existující záznamy této položky v databázy upravovány, FALSE, pokud ne
     * @var bool
     */
    protected const CAN_BE_UPDATED = true;
    
    /**
     * TRUE, pokud se jedná o záznam, který dosud není v databázi uložen
     * V takovém případě nemusí mít objekt při volání funkce save() nastavené ID a nelze na něm zavolat funkci load()
     * @var bool
     */
    protected $savedInDb;
    
    /**
     * TRUE, pokud jsou vlastnosti tohoto objektu právě ošetřovány proti XSS útoku metodou DatabaseItem::sanitize()
     * Tato vlastnost slouží pro zabránění rekurze
     * @var bool
     */
    private $sanitizationStarted = false;
    
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
    public abstract function initialize(): void;
    
    /**
     * Metoda ošetřující definované vlastnosti objektu proti XSS útoku
     * Pokud je vlastnost $sanitizationStarted nastavena na TRUE, nic se nestane
     * POZOR! Pokud byly po zavolání této metody ovlivňovány některé vlastnosti tohoto objektu, nebudou ošetřené, ale znovuzavolání této metody je také neoštří.
     * Tato metoda by proto měla být volána pouze jednou pro každý objekt a to těsně před výpisem dat do pohledu
     */
    public function sanitizeSelf(): void
    {
        //Zabraň rekurzi
        if ($this->sanitizationStarted === true) { return; }       //POZOR! Základní hodnota $this->sanitizationStarted není FALSE, ale undefined (definované FALSE je přepsáno v konstruktoru)
        $this->sanitizationStarted = true;
        
        //Ošetři postupně všechny definované vlastnosti objektu
        $sanitizer = new AntiXssSanitizer();
        $properties = $this->getDefinedProperties();
        foreach ($properties as $propertyName => $propertyValue)
        {
            $this->$propertyName = $sanitizer->sanitize($propertyValue);
        }
    }
    
    /**
     * Metoda navracející ID tohoto databázového záznamu
     * @return int ID záznamu
     */
    public function getId(): int
    {
        $this->loadIfNotLoaded($this->id);
        return $this->id;
    }
    
    /**
     * Metoda navracející údaj o tom, zda je tato položka již uložena v databázi, nebo zda se jedná o novou položku
     * @return bool TRUE, pokud se jedná o novou položku, FALSE, pokud je již položka se stejným ID uložena v databázi
     */
    public function isNew(): bool
    {
        return !$this->savedInDb;
    }
    
    /**
     * Metoda zjišťující, zda je daná proměnná definována (zda je do ní přiřazeno cokoliv jiného než objekt typu undefined
     * @param mixed $property
     * @return boolean TRUE, pokud proměnná obsahuje cokoliv jiného než objekt typu undefined (včetně null)
     */
    public function isDefined($property): bool
    {
        return (!$property instanceof undefined);
    }
    
    /**
     * Metoda načítající všechny vlastnosti objektu z databáze, pokud jakákoliv z vlastností objektů není definována
     */
    protected function loadIfNotAllLoaded(): void
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
    protected function loadIfNotLoaded($property): void
    {
        if (!$this->isDefined($property))
        {
            $this->load();
        }
    }
    
    /**
     * Metoda nastavující do vlastností objektu základní hodnoty, které by byly uloženy do databáze i v případě jejich nespecifikování v SQL INSERT dotazu
     * @param bool $overwriteAll TRUE, pokud mají být základními hodnotami přepsány všechny vlastnosti objektu, FALSE pouze pro přepsání vlastností, jejichž hodnota není nastavena (je nastavena na instanci třídy undefined)
     */
    protected function loadDefaultValues(bool $overwriteAll = false): void
    {
        foreach ($this::DEFAULT_VALUES as $fieldName => $fieldValue)
        {
            if (!$overwriteAll)
            {
                if ($this->isDefined($this->$fieldName)){ continue; }
            }
            $this->$fieldName = $fieldValue;
        }
    }
    
    /**
     * Metoda prověřující všechny vlastnosti objektu na jejich definovanost a navracející pole se jmény nedefinovaných vlastností
     * Jako nedefinovaná vlastnost se rozumí vlastnost, která ukládá instanci třídy undefined, vlastnost ukládající hodnotu NULL je definovaná
     * @return string[] Pole obsahující názvy vlastností, které nejsou definované jako klíče a instance třídy undefined jako hodnoty
     */
    protected function getUndefinedProperties(): array
    {
        //Ukládání nedefinovaných vlastností objektu
        return $this->getPropertyList(false);
    }
    
    /**
     * Metoda prověřující všechny vlastnosti objektu na jejich definovanost a navracející pole se jmény definovaných vlastností
     * Jako definovaná vlastnost se rozumí vlastnost, která ukládá cokoliv jiného než instanci třídy undefined, vlastnost ukládající hodnotu NULL je definovaná
     * @return string[] Pole obsahující názvy vlastností, které jsou definované jako klíče a jejich hodnoty jako hodnoty
     */
    protected function getDefinedProperties(): array
    {
        //Ukládání definovaných vlastností objektu
        return $this->getPropertyList(true);
    }
    
    /**
     * Metoda získávající buďto pole názvů definovaných vlastností objektu, nebo pole názvů nedefinovaných vlastností objektu
     * @param bool $getDefined TRUE, pokud má být navrácen seznam názvů definovaných vlastností, FALSE, pokud nedefinovaných
     * @return array Pole obsahující názvy definovaných nebo nedefinovaných vlastností objektu jako klíče a jejich hodnoty jako hodnoty
     */
    private function getPropertyList(bool $getDefined): array
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
     * Metoda načítající podle údajů uložených ve známých vlastnostech hodnoty všech ostatních vlastností z databáze
     * Z databáze jsou vybrány záznamy, které jejichž hodnoty odpovídají hodnotám uložených v definovaným vlastnostech objektu
     * @throws BadMethodCallException V případě, že není objekt zatím uložen v databázi, není z databáze navrácen ani jeden záznam odpovídající definovaným vlastnostem, nebo pokud jich je navrácených více
     * @return boolean TRUE, pokud jsou data položky úspěšně načtena
     */
    public function load(): bool
    {
        if ($this->savedInDb === false)
        {
            throw new BadMethodCallException('Cannot load data about an item that is\'t saved in the database yet');
        }
        
        //Získej seznam nedefinovaných vlastností
        $undefinedProperties = $this->getUndefinedProperties();
        
        $propertiesToLoad = array_keys($undefinedProperties);
        $columnsToLoad = array_intersect_key($this::COLUMN_DICTIONARY, array_flip($propertiesToLoad));
        $selectString = implode(',', $columnsToLoad);
        
        //Získej seznam definovaných vlastností, podle kterých se provede vyhledávání
        if ($this->isDefined($this->id))
        {
            //Je-li definováno ID, prováděj vyhledávání pouze podle něj
            $propertiesToFilterBy = array('id' => $this->id);
        }
        else
        {
            $propertiesToFilterBy = $this->getDefinedProperties();
        }
        
        foreach ($propertiesToFilterBy as $propertyName => $propertyValue)
        {
            unset($propertiesToFilterBy[$propertyName]);
            if (isset($this::COLUMN_DICTIONARY[$propertyName]))
            {
                //Filtruj pouze podle vlasností ukládaných v databázi
                $propertiesToFilterBy[$this::COLUMN_DICTIONARY[$propertyName]] = $propertyValue;
            }
        }
        
        $whereString = implode(' = ? AND ', array_keys($propertiesToFilterBy));
        $whereString .= ' = ?'; //Přidání rovnítka s otazníkem za název posledního sloupce
        $whereValues = array();
        foreach ($propertiesToFilterBy as $propertyValue)
        {
            if ($propertyValue instanceof DatabaseItem) { $whereValues[] = $propertyValue->getId(); } //Pro případ, že vlastnost ukládá odkaz na objekt
            else if ($propertyValue instanceof DateTime) { $whereValues[] = $propertyValue->format('Y-m-d H:i:s'); } //Pro případ, že vlastnost ukládá objekt typu DateTime
            else { $whereValues[] = $propertyValue; }
        }
        
        //Proveď SQL dotaz
        $query = 'SELECT '.$selectString.' FROM '.$this::TABLE_NAME.' WHERE '.$whereString.';';
        $result = Db::fetchQuery($query, $whereValues, true);
        if ($result === false) { throw new BadMethodCallException('No record in the database matches the search criteria, make sure the object is saved in the database'); }
        if (count($result) > 1) { throw new BadMethodCallException('More than one record in the database matches the search criteria, try to specify more properties'); }
        
        //Byl navrácen právě jeden výsledek
        $result = $result[0];
        
        foreach ($undefinedProperties as $propertyName => $propertyValue)
        {
            if (!isset($this::COLUMN_DICTIONARY[$propertyName]))
            {
                //Přiřazuj pouze do vlasností ukládaných v databázi
                continue;
            }
            
            if (isset($this::NON_PRIMITIVE_PROPERTIES[$propertyName]))
            {
                //Přiřazení do objektového datového typu
                if (is_subclass_of($this::NON_PRIMITIVE_PROPERTIES[$propertyName], __CLASS__))
                {
                    //Konstrukce databázového modelu
                    $ref = new ReflectionClass($this::NON_PRIMITIVE_PROPERTIES[$propertyName]);
                    $this->$propertyName = $ref->newInstanceArgs(array(false, $result[$this::COLUMN_DICTIONARY[$propertyName]]));
                }
                else if (is_subclass_of(self::NON_PRIMITIVE_PROPERTIES[$propertyName], 'DateTime'))
                {
                    //Konstrukce objektu DateTime
                    $this->$propertyName = new DateTime($result[$this::COLUMN_DICTIONARY[$propertyName]]);
                }
                else
                {
                    $message = '
                        Okay, I am not sure what you had to done to cause this,
                        but this error occured, because you saved a representation
                        of a non-primitive data type in the database and tried to
                        load item with this property. However, the program doesn\'t
                        know how to constuct the object just with the value loaded
                        from the database. If you want to fix this, you have to
                        manually add a new implementation for this class into the
                        DatabaseItem::load() method.
                    ';
                    throw new UnexpectedValueException($message);
                }
            }
            else
            {
                //Přiřazení primitivního datového typu
                $this->$propertyName = $result[$this::COLUMN_DICTIONARY[$propertyName]];
            }
        }
        return true;
    }
    
    /**
     * Metoda ukládající známá data této položky do databáze
     * Pokud se jedná o novou položku (vlastnost $savedInDb je nastavena na FALSE), je vložen nový záznam
     * V opačném případě jsou přepsána data položky se stejným ID
     * Neznámé vlastnosti (obsahující instanci undefined) nejsou ukládány
     * Tato metoda využívá metody DatabaseItem::create() a DatabaseItem::update(), přečtěte si i jejich phpDoc
     * @return boolean TRUE, pokud je položka úspěšně uložena do databáze
     * {@inheritDoc}
     * @see DatabaseItem::save()
     */
    public function save(): bool
    {
        if ($this->savedInDb)
        {
            //Aktualizace existujícího záznamu
            $result = $this->update();
        }
        else
        {
            //Vložení nového záznamu
            $result = $this->create();
        }
        return $result;
    }
    
    /**
     * Metoda vytvářející v databázové tabulce nový záznam s daty dané položky
     * I pokud je vyplněno ID nebo je vlastnost $savedInDb nastavena na TRUE, bude položka uložena jako nový záznam a vlastnost ID objektu bude přepsána
     * @throws BadMethodCallException Pokud některá z vlastností ukládaných do databáze není známa
     * @return boolean TRUE, pokud je úspěšně vytvořen v databázi nový záznam a ID položky nastaveno / aktualizováno, FALSE, pokud ne
     */
    protected function create(): bool
    {
        //Zkontroluj, zda je možné položku tohoto typu do databáze vložit
        if (!$this::CAN_BE_CREATED)
        {
            throw new BadMethodCallException('This item cannot be inserted into database, change value of CAN_BE_CREATED to change this');
        }
        
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
            else if ($propertyValue instanceof DateTime) { $databaseColumnValues[] = $propertyValue->format('Y-m-d H:i:s'); } //Pro případ, že vlastnost ukládá objekt typu DateTime
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
    protected function update(): bool
    {
        //Zkontrolovat, za je možné tuto položku v databázi upravovat
        if (!$this::CAN_BE_UPDATED)
        {
            throw new BadMethodCallException('Database data of this item cannot be modified, change value of CAN_BE_UPDATED to true to change this');
        }
        
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
                else if ($propertyValue instanceof  DateTime) { $databaseColumnValues[] = $propertyValue->format('Y-m-d H:i:s'); } //Pro případ, že vlastnost ukládá objekt typu DateTime
                else { $databaseColumnValues[] = $propertyValue; }
            }
        }
        
        if (count($databaseColumnNames) === 0) { return false; }
        $columnString = implode(' = ?,', $databaseColumnNames);
        $columnString .= ' = ?'; //Přidání rovnítka s otazníkem za název posledního sloupce
        
        return Db::executeQuery('UPDATE '.$this::TABLE_NAME.' SET '.$columnString.' WHERE '.$this::COLUMN_DICTIONARY['id'].' = ? LIMIT 1', array_pad($databaseColumnValues, count($databaseColumnValues) + 1, $this->id));
    }
    
    /**
     * Metoda odstraňující tuto položku z databáze
     * @return boolean TRUE, pokud je položka úspěšně odstraněna z databáze
     */
    public function delete(): bool
    {
    	$this->loadIfNotLoaded($this->id);
    	
    	Db::executeQuery('DELETE FROM '.$this::TABLE_NAME.' WHERE '.$this::COLUMN_DICTIONARY['id'].' = ? LIMIT 1;', array($this->id));
    	$this->id = new undefined();
    	$this->savedInDb = false;
    	return true;
    }
}

