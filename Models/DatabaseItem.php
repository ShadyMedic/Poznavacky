<?php
/**
 * Rozhraní pro třídy reprezentující záznamy v různých databázových tabulkách
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
    
    protected $id;
    
    /**
     * Konstruktor položky nastavující její ID nebo informaci o tom, že je nová
     */
    public abstract function __construct(int $id = 0);
    
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
     * Metoda odstraňující záznam reprezentovaný tímto objektem z databáze
     */
    public abstract function delete();
}