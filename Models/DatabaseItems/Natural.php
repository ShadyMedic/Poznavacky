<?php
namespace Poznavacky\Models\DatabaseItems;

use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\undefined;
use \RuntimeException;

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
     * @param int|undefined|null $picturesCount Počet obrázků nahraných k této přírodnině (při vyplnění parametru
     *     $pictures je ignorováno a je použita délka poskytnutého pole)
     * @param ClassObject|undefined|null $class Třída, se kterou je tato přírodnina svázána
     * @param Part[]|undefined|null $uses Pole částí poznávaček, ve kterých je tato přírodnina používána
     * @param int|undefined|null $usesCount Počet částí poznávaček, ve kterých je tato přírodnina využívána (při
     *     vyplnění parametru $uses je ignorováno a je použita délka poskytnutého pole)
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($name = null, $pictures = null, $picturesCount = null, $class = null, $uses = null,
                               $usesCount = null): void
    {
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($name === null) {
            $name = $this->name;
        }
        if ($pictures === null) {
            $pictures = $this->pictures;
            if ($picturesCount === null) {
                $picturesCount = $this->picturesCount;
            }
        } else {
            $picturesCount = count($pictures);
        }
        if ($class === null) {
            $class = $this->class;
        }
        if ($uses === null) {
            $uses = $this->uses;
            if ($usesCount === null) {
                $usesCount = $this->usesCount;
            }
        } else {
            $usesCount = count($uses);
        }
        
        $this->name = $name;
        $this->pictures = $pictures;
        $this->picturesCount = $picturesCount;
        $this->class = $class;
        $this->uses = $uses;
        $this->usesCount = $usesCount;
    }
    
    /**
     * Metoda navracející jméno této přírodniny
     * @return string Jméno přírodniny
     * @throws DatabaseException
     */
    public function getName(): string
    {
        $this->loadIfNotLoaded($this->name);
        return $this->name;
    }
    
    /**
     * Metoda navracející objekt třídy, do které tato přírodnina patří
     * @return ClassObject Třída, se kterou je tato přírodnina svázána
     * @throws DatabaseException
     */
    public function getClass(): ClassObject
    {
        $this->loadIfNotLoaded($this->class);
        return $this->class;
    }
    
    /**
     * Metoda navracející počet obrázků této přírodniny
     * @return int Počet obrázků této přírodniny uložené v databázi
     * @throws DatabaseException
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
     * @throws DatabaseException Pokud nastane chyba při práci s databází
     */
    public function getPictures(): array
    {
        if (!$this->isDefined($this->pictures)) {
            $this->loadPictures();
        }
        return $this->pictures;
    }
    
    /**
     * Metoda navracející náhodný obrázek této příodniny jako objekt
     * Pokud zatím nebyly adresy načteny z databáze, budou načteny.
     * @return Picture|null Náhodný obrázek této přírodniny nebo NULL, pokud k této přírodnině zatím nebyly přidány
     *     žádné obrázky
     * @throws DatabaseException Pokud se vyskytne chyba při práci s databází
     */
    public function getRandomPicture(): ?Picture
    {
        if (!$this->isDefined($this->pictures)) {
            $this->loadPictures();
        }
        if ($this->picturesCount === 0) {
            return null;
        }
        return $this->pictures[rand(0, $this->picturesCount - 1)];
    }
    
    /**
     * Metoda načítající z databáze obrázky přírodniny a ukládající je jako vlastnost objektu
     * Vlastnost $picturesCount je nastavena / upravena podle počtu načtených obrázků
     * @throws DatabaseException Pokud nastane chyba při práci s databází
     */
    public function loadPictures(): void
    {
        $this->loadIfNotLoaded($this->id);
        
        $result = Db::fetchQuery('SELECT '.Picture::COLUMN_DICTIONARY['id'].','.Picture::COLUMN_DICTIONARY['src'].','.
                                 Picture::COLUMN_DICTIONARY['enabled'].' FROM '.Picture::TABLE_NAME.' WHERE '.
                                 Picture::COLUMN_DICTIONARY['natural'].' = ?', array($this->id), true);
        if ($result === false || count($result) === 0) {
            //Žádné obrázky nenalezeny
            $this->pictures = array();
        } else {
            $this->pictures = array();
            
            foreach ($result as $pictureData) {
                $status = $pictureData[Picture::COLUMN_DICTIONARY['enabled']] === 1;
                $picture = new Picture(false, $pictureData[Picture::COLUMN_DICTIONARY['id']]);
                $picture->initialize($pictureData[Picture::COLUMN_DICTIONARY['src']], $this, $status);
                $this->pictures[] = $picture;
            }
        }
        $this->picturesCount = count($this->pictures);
    }
    
    /**
     * Metoda nastavující přírodnině nový název
     * Počítá se s tím, že jméno již bylo ošetřeno na délku, znaky a unikátnost
     * Změna není uložena do databáze, aby bylo nové jméno trvale uloženo, musí být zavolána metoda Natural::save()
     * @param string $newName Nový název pro tuto přírodninu
     */
    public function rename(string $newName): void
    {
        $this->name = $newName;
    }
    
    /**
     * Metoda převádějící všechny obrázky této přírodniny do jiné přírodniny a odstraňující tuto přírodninu z databáze
     * Není kontrolováno, zda přírodnina, ke které se mají obrázky převést patří do stejné třídy jako tato přírodnina
     * @param Natural $intoWhat Objekt přírodniny, ke které mají být převedeny všechny obrázky patřící do této
     *     přírodniny
     * @return array Asociativní pole obsahující klíče "mergedPictures" a "mergedUses" a počet sloučených obrázků a
     *     využití jako hodnoty
     * @throws DatabaseException
     */
    public function merge(Natural $intoWhat): array
    {
        //Převeď obrázky
        $mergedPictures = 0;
        if (!$this->isDefined($this->pictures)) {
            $this->loadPictures();
        }
        foreach ($this->pictures as $picture) {
            try {
                $picture->transfer($intoWhat);
                $picture->save();
                $mergedPictures++;
            } catch (RuntimeException $e) { /*Obrázek již v nové přírodnině existuje - přeskoč jej*/
            }
        }
        
        //Převeď využití
        $mergedUses = 0;
        $uses = $this->getUses();
        foreach ($uses as $use) {
            $naturals = $use->getNaturals();
            $contained = false;
            foreach ($naturals as $natural) {
                if ($natural->getId() === $intoWhat->getId()) {
                    $contained = true;
                }
            }
            # if ($contained) { /*V této části již nová přírodnina je - přeskoč ji*/ }
            if (!$contained) {
                //Aktualizuj ID přírodniny ve spojení části a přírodniny
                Db::executeQuery('UPDATE prirodniny_casti SET prirodniny_id = ? WHERE prirodniny_id = ? AND casti_id = ? LIMIT 1;',
                    array($intoWhat->getId(), $this->getId(), $use->getId()), false);
                $mergedUses++;
            }
        }
        
        $this->delete();
        return array('mergedPictures' => $mergedPictures, 'mergedUses' => $mergedUses);
    }
    
    /**
     * Metoda přidávající do databáze i do instance třídy nový obrázek této přírodniny
     * @param string $url Ošetřená adresa obrázku
     * @return boolean TRUE, pokud je obrázek přidán úspěšně, FALSE, pokud ne
     * @throws DatabaseException Pokud se obrázek nepodaří uložit
     */
    public function addPicture(string $url): bool
    {
        $picture = new Picture(true);
        $picture->initialize($url, $this);
        $result = $picture->save();
        if ($result) {
            $this->pictures[] = $picture;
            $this->picturesCount++; //Zvyš počet obrázků u této přírodniny v $_SESSION
            $uses = $this->getUses();
            foreach ($uses as $use) //Zvyš počet obrázků v této části v $_SESSION
            {
                $use->initialize(null, null, null, null, null, ($use->getPicturesCount() + 1));
            }
            //Zvýšení počtu obrázků u přírodniny v databázi je uděláno v databázi pomocí spouště
            //Zvýšení počtu obrázků u všech částí, ve kterých se tato přírodnina vyskytuje je uděláno v databázi pomocí spouště
            
            return true;
        }
        return false;
    }
    
    /**
     * Metoda kontrolující, zda je u této přírodniny již nahrán obrázek s danou adresou
     * Pokud zatím nebyly adresy načteny z databáze, budou načteny.
     * @param string $url Adresa obrázku, kterou hledáme
     * @return boolean TRUE, pokud tato přírodnina již má tento obrázek přidaný, FALSE, pokud ne
     * @throws DatabaseException
     */
    public function pictureExists(string $url): bool
    {
        if (!$this->isDefined($this->pictures)) {
            $this->loadPictures();
        }
        
        foreach ($this->pictures as $picture) {
            if ($picture->getSrc() === $url) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Metoda navracející počet částí, ve kterých je tato přírodnina používána
     * @return int Počet částí, které obsahují tuto přírodninu
     * @throws DatabaseException
     */
    public function getUsesCount(): int
    {
        if (!$this->isDefined($this->usesCount)) {
            $this->loadUses();
        }
        return $this->usesCount;
    }
    
    /**
     * Metoda navracející pole všech částí, ve kterých je tato přírodnina používána
     * Pokud zatím nebyl seznam částí načten z databáze, bude načten.
     * @return Part[] Pole částí, ve kterých je tato přírodnina využívána, jako objekty
     * @throws DatabaseException
     */
    public function getUses(): array
    {
        if (!$this->isDefined($this->uses)) {
            $this->loadUses();
        }
        return $this->uses;
    }
    
    /**
     * Metoda načítající z databáze části, ve kterých je přírodnina použita a ukládající je jako vlastnost objektu
     * Vlastnost $usesCount je nastavena / upravena podle počtu načtených obrázků
     * @throws DatabaseException
     */
    public function loadUses(): void
    {
        $this->loadIfNotLoaded($this->id);
        
        $result = Db::fetchQuery('SELECT '.Part::COLUMN_DICTIONARY['id'].', '.Part::COLUMN_DICTIONARY['name'].', '.
                                 Part::COLUMN_DICTIONARY['url'].', '.Part::COLUMN_DICTIONARY['group'].' FROM '.
                                 Part::TABLE_NAME.' WHERE '.Part::COLUMN_DICTIONARY['id'].
                                 ' IN (SELECT casti_id FROM prirodniny_casti WHERE prirodniny_id = ?)',
            array($this->id), true);
        if ($result === false || count($result) === 0) {
            //Žádná využití nenalezena
            $this->uses = array();
        } else {
            $this->uses = array();
            
            foreach ($result as $partData) {
                $part = new Part(false, $partData[Part::COLUMN_DICTIONARY['id']]);
                $part->initialize($partData[Part::COLUMN_DICTIONARY['name']], $partData[Part::COLUMN_DICTIONARY['url']],
                    $partData[Part::COLUMN_DICTIONARY['group']]);
                $this->uses[] = $part;
            }
        }
        $this->usesCount = count($this->uses);
    }
}

