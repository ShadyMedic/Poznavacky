<?php
/** 
 * Třída reprezentující objekt obrázku
 * @author Jan Štěch
 */
class Picture extends DatabaseItem
{
    public const TABLE_NAME = 'prirodniny';
    
    protected const DEFAULT_VALUES = array(
        'enabled' => true
    );
    
    private $src;
    private $natural;
    private $part;
    protected $enabled;
    
    private $reports;
    
    /**
     * Konstruktor obrázku nastavující jeho ID nebo informaci o tom, že je nový
     * @param bool $isNew FALSE, pokud je již obrázek se zadaným ID nebo později doplněnými informacemi uložen v databázi, TRUE, pokud se jedná o nový obrázek
     * @param int $id ID obrázku (možné pouze pokud je první argument FALSE; pokud není vyplněno, bude načteno z databáze po vyplnění dalších údajů o ní pomocí metody Picture::initialize())
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
     * @param string|undefined|null $src Adresa, pod kterou lze obrázek najít
     * @param Natural|undefined|null $natural Odkaz na objekt přírodniny, kterou tento obrázek zobrazuje
     * @param Part|undefined|null $part Odkaz na objekt části, ve které se obrázek v současnosti nachází
     * @param bool|undefined|null $enabled TRUE, pokud je obrázek povolen, FALSE, pokud je skryt
     * @param Report[]|undefined|null $reports Pole hlášení tohoto obrázku, jako objekty
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($src = null, $natural = null, $part = null, $enabled = null, $reports = null)
    {
        //Načtení defaultních hodnot do nenastavených vlastností
        $this->loadDefaultValues();
        
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($src === null){ $src = $this->src; }
        if ($natural === null){ $natural = $this->natural; }
        if ($part === null){ $part = $this->part; }
        if ($enabled === null){ $enabled = $this->enabled; }
        if ($reports === null){ $reports = $this->reports; }
        
        $this->src = $src;
        $this->natural = $natural;
        $this->part = $part;
        $this->enabled = $enabled;
        $this->reports = $reports;
    }
    
    /**
     * Metoda načítající z databáze všechny vlastnosti objektu s výjimkou seznamu hlášení tohoto obrázku, podle ID (pokud je vyplněno)
     * V případě, že není známé ID, ale je známá adresa obrázku a přírodnina nebo část, do které patří, jsou načteny ty samé informace + ID podle těchto známých informací
     * Hlášení tohoto obrázku lze načíst samostatně pomocí metody Picture::loadReports()
     * @throws BadMethodCallException Pokud se jedná o obrázek, který dosud není uložen v databázi nebo pokud není o objektu známo dost informací potřebných pro jeho načtení
     * @throws NoDataException Pokud není obrázek nalezen v databázi
     * @return boolean TRUE, pokud jsou vlastnosti tohoto obrázku úspěšně načteny z databáze
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
            $result = Db::fetchQuery('SELECT zdroj, prirodniny_id, casti_id, povoleno FROM '.self::TABLE_NAME.' WHERE obrazky_id = ? LIMIT 1', array($this->id));
            if (empty($result))
            {
                throw new NoDataException(NoDataException::UNKNOWN_PICTURE);
            }
            
            $src = $result['zdroj'];
            $natural = new Natural(false, $result['prirodniny_id']);
            $part = new Part(false, $result['casti_id']);
        }
        else if ($this->isDefined($this->src) && $this->isDefined($this->natural))
        {
            $result = Db::fetchQuery('SELECT obrazky_id, casti_id, povoleno FROM '.self::TABLE_NAME.' WHERE zdroj = ? AND prirodniny_id = ? LIMIT 1', array($this->src, $this->natural->getId()));
            if (empty($result))
            {
                throw new NoDataException(NoDataException::UNKNOWN_PICTURE);
            }
            $this->id = $result['obrazky_id'];
            $src = null;
            $natural = null;
            $part = new Part(false, $result['casti_id']);
        }
        else if ($this->isDefined($this->src) && $this->isDefined($this->part))
        {
            $result = Db::fetchQuery('SELECT obrazky_id, prirodniny_id, povoleno FROM '.self::TABLE_NAME.' WHERE zdroj = ? AND casti_id = ? LIMIT 1', array($this->src, $this->part->getId()));
            if (empty($result))
            {
                throw new NoDataException(NoDataException::UNKNOWN_PICTURE);
            }
            $this->id = $result['obrazky_id'];
            $src = null;
            $natural = new Natural(false, $result['prirodniny_id']);
            $part = null;
        }
        else
        {
            throw new BadMethodCallException('Not enough properties are know about the item to be able to load the rest');
        }
        
        $enabled = ($result['povoleno'] === 1) ? true : false;
        
        $this->initialize($src, $natural, $part, $enabled, null);
        
        return true;
    }
    
    /**
     * Metoda ukládající data tohoto obrázku do databáze
     * Pokud se jedná o nový obrázek (vlastnost $savedInDb je nastavena na FALSE), je vložen nový záznam
     * V opačném případě jsou přepsána data obrázku se stejným ID
     * @throws BadMethodCallException Pokud se nejedná o nový obrázek a zároveň není známo jeho ID (znalost ID obrázku je nutná pro modifikaci databázové tabulky)
     * @return boolean TRUE, pokud je obrázek úspěšně uložen do databáze
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
            //Aktualizace existujícího obrázku
            $this->loadIfNotAllLoaded();
            
            $result = Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET zdroj = ?, prirodniny_id = ?, casti_id = null, povoleno = ? WHERE obrazky_id = ? LIMIT 1', array($this->src, $this->natural->getId(), $this->part->getId(), $this->enabled, $this->id));
        }
        else
        {
            //Tvorba nové pozvánky
            $this->id = Db::executeQuery('INSERT INTO '.self::TABLE_NAME.' (zdroj,prirodniny_id,casti_id,povoleno) VALUES (?,?,?,?)', array($this->src, $this->natural->getId(), $this->part->getId(), $this->enabled), true);
            if (!empty($this->id))
            {
                $this->savedInDb = true;
                $result = true;
            }
        }
        return $result;
    }
    
    /**
     * Metoda navracející ID tohoto obrázku
     * @return int ID obrázku
     */
    public function getId()
    {
        $this->loadIfNotLoaded($this->id);
        return $this->id;
    }
    
    /**
     * Metoda navracející URL adresu toho obrázku
     * @return string Zdroj (URL) obrázku
     */
    public function getSrc()
    {
        $this->loadIfNotLoaded($this->src);
        return $this->src;
    }
    
    /**
     * Metoda navracející objekt přírodniny, kterou zachycuje tento obrázek
     * @return Natural Přírodnina na obrázku
     */
    public function getNatural()
    {
        $this->loadIfNotLoaded($this->natural);
        return $this->natural;
    }
    
    /**
     * Metoda navracející stav obrázku
     * @return bool TRUE, je-li obrázek povolený, FALSE, pokud je skrytý
     */
    public function isEnabled()
    {
        $this->loadIfNotLoaded($this->enabled);
        return $this->enabled;
    }
    
    //TODO - upravit metodu tak, aby jako první parametr brala proměnnou typu Natural
    /**
     * Metoda upravující přírodninu a adresu tohoto obrázku z rozhodnutí administrátora
     * @param Natural $newNatural Objekt reprezentující nově zvolenou přírodninu
     * @param string $newUrl Nová adresa k obrázku
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem nebo jsou zadaná data neplatná
     * @return boolean TRUE, pokud jsou údaje tohoto obrázku úspěšně aktualizovány
     */
    public function updatePicture(string $newNaturalName, string $newUrl)
    {
        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        if (!AccessChecker::checkSystemAdmin())
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
        
        $this->loadIfNotLoaded($this->natural);
        $this->loadIfNotLoaded($this->id);
        
        //Kontrola, zda daná nová URL adresa vede na obrázek a zda je nová přírodnina součástí té samé poznávačky, jako ta stará
        $checker = new PictureAdder($this->natural->getPart()->getGroup());
        $newNatural = $checker->checkData($newNaturalName, $newUrl);  //Pokud nejsou data v pořádku, nastane výjimka a kód nepokračuje
        
        //Kontrola dat OK
        
        //Upravit údaje v databázi
        Db::connect();
        Db::executeQuery('UPDATE obrazky SET prirodniny_id = ?, zdroj = ? WHERE obrazky_id = ? LIMIT 1', array($newNatural->getId(), $newUrl, $this->id));
        
        //Aktualizovat údaje ve vlastnostech této instance
        $this->natural = $newNatural;
        $this->src = $newUrl;
        
        return true;
    }
    
    /**
     * Metoda odstraňující z databáze všechna hlášení vztahující se k tomuto obrázku
     * Vlastnost pole uchovávající hlášení tohoto obrázku, které je uložené jako vlastnost je nahrazeno prázdným polem
     * @return boolean TRUE, pokud jsou hlášení úspěšně odstraněna
     */
    public function deleteReports()
    {
        Db::connect();
        Db::executeQuery('DELETE FROM hlaseni WHERE obrazky_id = ?', array($this->id));
        $this->reports = array();
        return true;
    }
    
    /**
     * Metoda navracející pole hlášení tohoto obrázku
     * Pokud hlášení zatím nebyla načtena z databáze, budou před navrácením načtena
     * @return Report[] Pole hlášení tohoto obrázku jako objekty
     */
    public function getReports()
    {
        if (!$this->isDefined($this->reports)){ $this->loadReports(); }
        return $this->reports;
    }
    
    /**
     * Metoda načítající hlášení tohoto obrázku z databáze a ukládající je do vlastnosti této instance jako objekty
     */
    public function loadReports()
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT hlaseni_id,duvod,dalsi_informace,pocet FROM hlaseni WHERE obrazky_id = ?', array($this->id), true);
        
        if (count($result) === 0)
        {
            //Žádná hlášení tohoto obrázku
            $this->reports = array();
            return;
        }
        
        foreach ($result as $reportInfo)
        {
            //Konstrukce nových objektů hlášení a jejich ukládání do pole
            $this->reports[] = new Report($reportInfo['hlaseni_id'], $this, $reportInfo['duvod'], $reportInfo['dalsi_informace'], $reportInfo['pocet']);
        }
    }
    
    /**
     * Metoda skrývající tento obrázek v databázi
     * Tato metoda může být použita pouze v případě, že právě přihlášený uživatel je systémový administrátor
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem
     * @return boolean TRUE, pokud je obrázek úspěšně skryt v databázi
     */
    public function disable()
    {
        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        if (!AccessChecker::checkSystemAdmin())
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
        
        //Kontrola dat OK
        
        $this->loadIfNotLoaded($this->id);
        
        //Vypnout obrázek v databázi
        Db::connect();
        Db::executeQuery('UPDATE obrazky SET povoleno = 0 WHERE obrazky_id = ? LIMIT 1;', array($this->id));
        
        //Přenastavit vlastnost této instance
        $this->enabled = false;
    }
    
    /**
     * Metoda odstraňující tento obrázek z databáze
     * @return boolean TRUE, pokud je obrázek úspěšně odstraněn z databáze
     * {@inheritDoc}
     * @see DatabaseItem::delete()
     */
    public function delete()
    {
    	$this->loadIfNotLoaded($this->id);
    	
    	Db::connect();
    	Db::executeQuery('DELETE FROM '.self::TABLE_NAME.' WHERE obrazky_id = ? LIMIT 1;', array($this->id));
    	$this->id = new undefined();
    	$this->savedInDb = false;
    	return true;
    }
}