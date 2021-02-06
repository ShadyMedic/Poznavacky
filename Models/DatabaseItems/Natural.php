<?php
namespace Poznavacky\Models\DatabaseItems;

use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\undefined;

/**
 * Třída reprezentující objekt přírodniny
 * @author Jan Štěch
 */
class Natural extends DatabaseItem
{
    public const TABLE_NAME = 'prirodniny';

    public const COLUMN_DICTIONARY = array(
        'id' => 'prirodniny_id',
        'name' => 'nazev',
        'picturesCount' => 'obrazky',
        'class' => 'tridy_id'
    );

    protected const NON_PRIMITIVE_PROPERTIES = array(
        'class' => ClassObject::class
    );

    protected const DEFAULT_VALUES = array(
        'picturesCount' => 0,
        'usesCount' => 0
    );

    protected const CAN_BE_CREATED = true;
    protected const CAN_BE_UPDATED = true;

    protected $name;
    protected $picturesCount;
    protected $class;

    protected $pictures;
    protected $uses;
    protected $usesCount;

    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * Při nastavení některého z argumentů na undefined, je hodnota dané vlastnosti také nastavena na undefined
     * Při nastavení některého z argumentů na null, není hodnota dané vlastnosti nijak pozměněna
     * @param string|undefined|null $name Název této přírodniny
     * @param Picture[]|undefined|null $pictures Pole obrázků nahraných k této přírodnině, jako objekty
     * @param int|undefined|null $picturesCount Počet obrázků nahraných k této přírodnině (při vyplnění parametru $pictures je ignorováno a je použita délka poskytnutého pole)
     * @param ClassObject|undefined|null $class Třída, se kterou je tato přírodnina svázána
     * @param Part[]|undefined|null $uses Pole částí poznávaček, ve kterých je tato přírodnina používána
     * @param int|undefined|null $usesCount Počet částí poznávaček, ve kterých je tato přírodnina využívána (při vyplnění parametru $uses je ignorováno a je použita délka poskytnutého pole)
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($name = null, $pictures = null, $picturesCount = null, $class = null, $uses = null, $usesCount = null): void
    {
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($name === null){ $name = $this->name; }
        if ($pictures === null)
        {
            $pictures = $this->pictures;
            if ($picturesCount === null){ $picturesCount = $this->picturesCount; }
        }
        else { $picturesCount = count($pictures); }
        if ($class === null){ $class = $this->class; }
        if ($uses === null)
        {
            $uses = $this->uses;
            if ($usesCount === null){ $usesCount = $this->usesCount; }
        }
        else { $usesCount = count($uses); }

        $this->name = $name;
        $this->pictures = $pictures;
        $this->picturesCount = $picturesCount;
        $this->class = $class;
    }

    /**
     * Metoda navracející jméno této přírodniny
     * @return string Jméno přírodniny
     */
    public function getName(): string
    {
        $this->loadIfNotLoaded($this->name);
        return $this->name;
    }

    /**
     * Metoda navracející objekt třídy, do které tato přírodnina patří
     * @return ClassObject Třída, se kterou je tato přírodnina svázána
     */
    public function getClass(): ClassObject
    {
        $this->loadIfNotLoaded($this->class);
        return $this->class;
    }

    /**
     * Metoda navracející počet obrázků této přírodniny
     * @return int Počet obrázků této přírodniny uložené v databázi
     */
    public function getPicturesCount(): int
    {
        $this->loadIfNotLoaded($this->picturesCount);
        return $this->picturesCount;
    }
    
    /**
     * Metoda navracející pole všech obrázků této přírodniny jako objekty
     * Pokud zatím nebyly adresy načteny z databáze, budou načteny.
     * @return Picture[] Pole obrázků této přírodniny z databáze jako objekty
     */
    public function getPictures(): array
    {
        if (!$this->isDefined($this->pictures)){ $this->loadPictures(); }
        return $this->pictures;
    }

    /**
     * Metoda navracející náhodný obrázek této příodniny jako objekt
     * Pokud zatím nebyly adresy načteny z databáze, budou načteny.
     * @return Picture|null Náhodný obrázek této přírodniny nebo NULL, pokud k této přírodnině zatím nebyly přidány žádné obrázky
     */
    public function getRandomPicture()
    {
        if (!$this->isDefined($this->pictures)){ $this->loadPictures(); }
        if ($this->picturesCount === 0) { return null; }
        return $this->pictures[rand(0, $this->picturesCount - 1)];
    }
    
    /**
     * Metoda načítající z databáze obrázky přírodniny a ukládající je jako vlastnost objektu
     * Vlastnost $picturesCount je nastavena / upravena podle počtu načtených obrázků
     */
    public function loadPictures(): void
    {
        $this->loadIfNotLoaded($this->id);
        
        $result = Db::fetchQuery('SELECT '.Picture::COLUMN_DICTIONARY['id'].','.Picture::COLUMN_DICTIONARY['src'].','.Picture::COLUMN_DICTIONARY['enabled'].' FROM '.Picture::TABLE_NAME.' WHERE '.Picture::COLUMN_DICTIONARY['natural'].' = ?', array($this->id), true);
        if ($result === false || count($result) === 0)
        {
            //Žádné obrázky nenalezeny
            $this->pictures = array();
        }
        else
        {
            $this->pictures = array();
            
            foreach ($result as $pictureData)
            {
                $status = ($pictureData[Picture::COLUMN_DICTIONARY['enabled']] === 1) ? true : false;
                $picture = new Picture(false, $pictureData[Picture::COLUMN_DICTIONARY['id']]);
                $picture->initialize($pictureData[Picture::COLUMN_DICTIONARY['src']], $this, $status, null);
                $this->pictures[] = $picture;
            }
        }
        $this->picturesCount = count($this->pictures);
    }
    
    /**
     * Metoda přidávající do databáze i do instance třídy nový obrázek této přírodniny
     * @param string $url Ošetřená adresa obrázku
     * @return boolean TRUE, pokud je obrázek přidán úspěšně, FALSE, pokud ne
     */
    public function addPicture(string $url): bool
    {   
        $picture = new Picture(true);
        $picture->initialize($url, $this, null, null);
        $result = $picture->save();
        if ($result)
        {
            $this->pictures[] = $picture;
            return true;
        }
        return false;
    }
    
    /**
     * Metoda kontrolující, zda je u této přírodniny již nahrán obrázek s danou adresou
     * Pokud zatím nebyly adresy načteny z databáze, budou načteny.
     * @param string $url Adresa obrázku, kterou hledáme
     * @return boolean TRUE, pokud tato přírodnina již má tento obrázek přidaný, FALSE, pokud ne
     */
    public function pictureExists(string $url): bool
    {
        if (!$this->isDefined($this->pictures)){ $this->loadPictures(); }
        
        foreach ($this->pictures as $picture)
        {
            if ($picture->getSrc() === $url)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Metoda navracející počet částí, ve kterých je tato přírodnina používána
     * @return int Počet částí, které obsahují tuto přírodninu
     */
    public function getUsesCount(): int
    {
        if (!$this->isDefined($this->usesCount)){ $this->loadUses(); }
        return $this->usesCount;
    }

    /**
     * Metoda navracející pole všech částí, ve kterých je tato přírodnina používána
     * Pokud zatím nebyl seznam částí načten z databáze, bude načten.
     * @return Part[] Pole částí, ve kterých je tato přírodnina využívána, jako objekty
     */
    public function getUses(): array
    {
        if (!$this->isDefined($this->uses)){ $this->loadUses(); }
        return $this->uses;
    }

    /**
     * Metoda načítající z databáze části, ve kterých je přírodnina použita a ukládající je jako vlastnost objektu
     * Vlastnost $usesCount je nastavena / upravena podle počtu načtených obrázků
     */
    public function loadUses(): void
    {
        $this->loadIfNotLoaded($this->id);

        $result = Db::fetchQuery('SELECT '.Part::COLUMN_DICTIONARY['id'].', '.Part::COLUMN_DICTIONARY['name'].', '.Part::COLUMN_DICTIONARY['url'].', '.Part::COLUMN_DICTIONARY['group'].' FROM '.Part::TABLE_NAME.' WHERE '.Part::COLUMN_DICTIONARY['id'].' IN (SELECT casti_id FROM prirodniny_casti WHERE prirodniny_id = ?)', array($this->id), true);
        if ($result === false || count($result) === 0)
        {
            //Žádná využití nenalezena
            $this->uses = array();
        }
        else
        {
            $this->uses = array();

            foreach ($result as $partData)
            {
                $part = new Part(false, $partData[Part::COLUMN_DICTIONARY['id']]);
                $part->initialize($partData[Part::COLUMN_DICTIONARY['name']], $partData[Part::COLUMN_DICTIONARY['url']], $partData[Part::COLUMN_DICTIONARY['group']]);
                $this->uses[] = $part;
            }
        }
        $this->usesCount = count($this->uses);
    }
}

