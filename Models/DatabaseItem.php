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
        $properties = get_object_vars($this);
        foreach ($properties as $property)
        {
            $this->$property = new Undefined();
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
    public function loadDefaultValues(bool $overwriteAll = false)
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
     * Metoda načítající podle údajů poskytnutých v konstruktoru všechny ostatní vlastnosti objektu
     */
    public abstract function load();
    
    /**
     * Metoda ukládající všechny vlastnosti objektu do databáze, přepisujíce záznam se stejným ID nebo vytvářející nový v případě, že záznam s takovým ID v databázové tabulce neexistuje
     * Před uložením jsou do nevyplněných vlastností načteny základní hodnoty
     */
    public abstract function save();
    
    /**
     * Metoda odstraňující záznam reprezentovaný tímto objektem z databáze a nulující vlastnost ID objektu
     */
    public abstract function delete();
}