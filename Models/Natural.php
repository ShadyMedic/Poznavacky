<?php
/** 
 * Třída reprezentující objekt přírodniny
 * @author Jan Štěch
 */
class Natural extends DatabaseItem
{
    public const TABLE_NAME = 'prirodniny';
    
    protected const DEFAULT_VALUES = array(
        'picturesCount' => 0
    );
    
    private $name;
    protected $picturesCount;
    private $group;
    private $part;

    private $pictures;
    
    /**
     * Konstruktor přírodniny nastavující její ID nebo informaci o tom, že je nová
     * @param bool $isNew FALSE, pokud je již přírodnina se zadaným ID nebo později doplněnými informacemi uložena v databázi, TRUE, pokud se jedná o novou přírodninu
     * @param int $id ID přírodniny (možné pouze pokud je první argument FALSE; pokud není vyplněno, bude načteno z databáze po vyplnění dalších údajů o ní pomocí metody Natural::initialize())
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function __construct(bool $isNew, int $id = 0)
    {
        parent::__construct($isNew, $id);
    }
    
    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * Při nastavení některého z argumentů na undefined, je hodnota dané vlastnosti také nastavena na undefined
     * Při nastavení některého z argumentů na null, není hodnota dané vlastnosti nijak pozměněna
     * @param string|undefined|null $name Název této přírodniny
     * @param Picture[]|undefined|null $pictures Pole obrázků nahraných k této přírodnině, jako objekty
     * @param int|undefined|null $picturesCount Počet obrázků nahraných k této přírodnině (při vyplnění parametru $pictures je ignorováno a je použita délka poskytnutého pole)
     * @param Group|undefined|null $group Poznávačka, do které tato přírodnina patří
     * @param Part|undefined|null $part Část poznávačky, do které je tato přírodnina v současné době přiřazena
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($name = null, $pictures = null, $picturesCount = null, $group = null, $part = null)
    {
        //Načtení defaultních hodnot do nenastavených vlastností
        $this->loadDefaultValues();
        
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($name === null){ $name = $this->name; }
        if ($pictures === null)
        {
            $pictures = $this->pictures;
            if ($picturesCount === null){ $picturesCount = $this->picturesCount; }
        }
        else { $picturesCount = count($pictures); }
        if ($group === null){ $group = $this->group; }
        if ($part === null){ $part = $this->part; }
        
        $this->name = $name;
        $this->pictures = $pictures;
        $this->picturesCount = $picturesCount;
        $this->group = $group;
        $this->part = $part;
    }
    
    /**
     * Metoda načítající z databáze všechny vlastnosti objektu s výjimkou seznamu obrázků, které jsou k této přírodnině nahrány, podle ID (pokud je vyplněno)
     * V případě, že není známé ID, ale je známý název přírodniny a část nebo poznávačka, do které patří, jsou načteny ty samé informace + ID podle těchto známých informací
     * Obrázky, které byly nahrány k této přírodnině mohou být načteny zvlášť pomocí metody Natural::loadPictures()
     * @throws BadMethodCallException Pokud se jedná o přírodninu, která dosud není uložena v databázi nebo pokud není o objektu známo dost informací potřebných pro jeho načtení
     * @throws NoDataException Pokud není přírodnina se zadanými vlastnostmi nalezena v databázi
     * @return boolean TRUE, pokud jsou vlastnosti této přírodniny úspěšně načteny z databáze
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
            $result = Db::fetchQuery('SELECT nazev, obrazky, poznavacky_id, casti_id FROM '.self::TABLE_NAME.' WHERE prirodniny_id = ? LIMIT 1', array($this->id));
            if (empty($result))
            {
                throw new NoDataException(NoDataException::UNKNOWN_NATURAL);
            }
            
            $name = $result['nazev'];
            $picturesCount = $result['obrazky'];
            $group = new Group(false, $result['poznavacky_id']);
            $part = new Part(false, $result['casti_id']);
            
            $this->initialize($name, null, $picturesCount, $group, $part);
        }
        else if ($this->isDefined($this->name) && $this->isDefined($this->group))
        {
            $result = Db::fetchQuery('SELECT prirodniny_id, obrazky, casti_id FROM '.self::TABLE_NAME.' WHERE poznavacky_id = ? LIMIT 1', array($this->group->getId()));
            if (empty($result))
            {
                throw new NoDataException(NoDataException::UNKNOWN_NATURAL);
            }
            
            $this->id = $result['prirodniny_id'];
            $picturesCount = $result['obrazky'];
            $part = new Part(false, $result['casti_id']);
            
            $this->initialize(null, null, $picturesCount, null, $part);
        }
        else if ($this->isDefined($this->name) && $this->isDefined($this->part))
        {
            $result = Db::fetchQuery('SELECT prirodniny_id, obrazky, poznavacky_id FROM '.self::TABLE_NAME.' WHERE casti_id = ? LIMIT 1', array($this->part->getId()));
            if (empty($result))
            {
                throw new NoDataException(NoDataException::UNKNOWN_NATURAL);
            }
            
            $this->id = $result['prirodniny_id'];
            $picturesCount = $result['obrazky'];
            $group = new Group(false, $result['poznavacky_id']);
            
            $this->initialize(null, null, $picturesCount, $group, null);
        }
        else
        {
            throw new BadMethodCallException('Not enough properties are know about the item to be able to load the rest');
        }
        return true;
    }
    
    /**
     * Metoda ukládající data této přírodniny do databáze
     * Pokud se jedná o novou přírodninu (vlastnost $savedInDb je nastavena na FALSE), je vložen nový záznam
     * V opačném případě jsou přepsána data přírodniny se stejným ID
     * @throws BadMethodCallException Pokud se nejedná o novou přírodninu a zároveň není známo jeho ID (znalost ID pozvánky je nutná pro modifikaci databázové tabulky)
     * @return boolean TRUE, pokud je přírodnina úspěšně uložena do databáze
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
            //Aktualizace existující přírodniny
            $this->loadIfNotAllLoaded();
            
            $result = Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET nazev = ?, obrazky = ?, poznavacky_id = ?, casti_id = ? WHERE prirodniny_id = ? LIMIT 1', array($this->name, $this->picturesCount, $this->group->getId(), $this->part->getId(), $this->id));
        }
        else
        {
            //Tvorba nové přírodniny
            $this->id = Db::executeQuery('INSERT INTO '.self::TABLE_NAME.' (nazev,obrazky,poznavacky_id,casti_id) VALUES (?,?,?,?)', array($this->name, $this->picturesCount, $this->group->getId(), $this->part->getId()), true);
            if (!empty($this->id))
            {
                $this->savedInDb = true;
                $result = true;
            }
        }
        return $result;
    }
    
    /**
     * Metoda navracející ID této přírodniny
     * @return int ID této přírodniny
     */
    public function getId()
    {
        $this->loadIfNotLoaded($this->id);
        return $this->id;
    }
    
    /**
     * Metoda navracející jméno této přírodniny
     * @return string Jméno přírodniny
     */
    public function getName()
    {
        $this->loadIfNotLoaded($this->name);
        return $this->name;
    }
    
    /**
     * Metoda navracející objekt části, do které je tato přírodnina přiřazena
     * @return Part Část, do které přírodnina spadá
     */
    public function getPart()
    {
        $this->loadIfNotLoaded($this->part);
        return $this->part;
    }
    
    /**
     * Metoda navracející počet obrázků této přírodniny
     * @return int Počet obrázků této přírodniny uložené v databázi
     */
    public function getPicturesCount()
    {
        $this->loadIfNotLoaded($this->picturesCount);
        return $this->picturesCount;
    }
    
    /**
     * Metoda navracející pole všech obrázků této přírodniny jako objekty
     * Pokud zatím nebyly adresy načteny z databáze, budou načteny.
     * @return Picture[] Pole obrázků této přírodniny z databáze jako objekty
     */
    public function getPictures()
    {
        if (!$this->isDefined($this->pictures)){ $this->loadPictures(); }
        return $this->pictures;
    }
    
    /**
     * Metoda navracející náhodný obrázek této příodniny jako objekt
     * Pokud zatím nebyly adresy načteny z databáze, budou načteny.
     * @return Picture Náhodný obrázek této přírodniny
     */
    public function getRandomPicture()
    {
        if (!$this->isDefined($this->pictures)){ $this->loadPictures(); }
        return $this->pictures[rand(0, $this->picturesCount - 1)];
    }
    
    /**
     * Metoda načítající z databáze obrázky přírodniny a ukládající je jako vlastnost objektu
     */
    public function loadPictures()
    {
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        $result = Db::fetchQuery('SELECT obrazky_id,zdroj,povoleno FROM obrazky WHERE prirodniny_id = ?', array($this->id), true);
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
                $status = ($pictureData['povoleno'] === 1) ? true : false;
                $this->pictures[] = new Picture($pictureData['obrazky_id'], $pictureData['zdroj'], $this, $status);
            }
        }
    }
    
    /**
     * Metoda přidávající do databáze i do instance třídy nový obrázek této přírodniny
     * @param string $url Ošetřená adresa obrázku
     * @return boolean TRUE, pokud je obrázek přidán úspěšně, FALSE, pokud ne
     */
    public function addPicture(string $url)
    {
        $this->loadIfNotLoaded($this->id);
        $this->loadIfNotLoaded($this->part);
        
        Db::connect();
        $result = Db::executeQuery('INSERT INTO obrazky (prirodniny_id,zdroj,casti_id) VALUES (?,?,?)', array($this->id, $url, $this->part->getId()), true);
        if ($result)
        {
            $this->picturesCount++;
            $this->pictures[] = new Picture($result, $url, $this, true);
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
    public function pictureExists(string $url)
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
     * Metoda odstraňující tuto přírodninu z databáze
     * @return boolean TRUE, pokud je přírodnina úspěšně odstraněna z databáze
     * {@inheritDoc}
     * @see DatabaseItem::delete()
     */
    public function delete()
    {
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        Db::executeQuery('DELETE FROM '.self::TABLE_NAME.' WHERE prirodniny_id = ? LIMIT 1;', array($this->id));
        $this->id = new undefined();
        $this->savedInDb = false;
        return true;
    }
}