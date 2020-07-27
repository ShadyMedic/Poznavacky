<?php
/**
 * Abstraktní mateřská třída pro třídy reprezentující záznamy v různých databázových tabulkách
 * @author Jan Štěch
 */
abstract class DatabaseItem
{
    /**
     * TRUE, pokud se jedná o záznam, který dosud není v databázi uložen
     * V takovém případě nemusí mít objekt při volání funkce save() nastavené ID a nelze na něm zavolat funkci load()
     * @var boolean
     */
    protected $savedInDb;
    
    /**
     * Název databázové tabulky, která skladuje záznamy typu této třídy
     * Všechny třídy dědící z této abstraktní třídy musí definovat tuto konstantu
     * @var string
     */
    protected const TABLE_NAME = null;
    
    protected $id;
    
    /**
     * Konstruktor položky nastavující její ID nebo informaci o tom, že je nová
     * Tento konstruktor je volán z konstruktorů všech tříd, které z této abstraktní tříd dědí
     * @param bool $isNew FALSE, pokud je již položka se zadaným ID nebo později doplněnými informacemi uložena v databázi, TRUE, pokud se jedná o novou položku
     * @param int $id ID položky (pouze pokud je první argument FALSE)
     */
    public function __construct(bool $isNew, int $id = 0)
    {
        if ($isNew)
        {
            //Nová položka bez známých informací
            $this->savedInDb = false;
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
     */
    public abstract function initialize();
    
    /**
     * Metoda načítající podle údajů poskytnutých v konstruktoru všechny ostatní vlastnosti objektu
     */
    public abstract function load();
    
    /**
     * Metoda ukládající všechny vlastnosti objektu do databáze, přepisujíce záznam se stejným ID nebo vytvářející nový v případě, že záznam s takovým ID v databázové tabulce neexistuje
     */
    public abstract function save();
    
    /**
     * Metoda odstraňující záznam reprezentovaný tímto objektem z databáze a nulující vlastnost ID objektu
     */
    public abstract function delete();
}