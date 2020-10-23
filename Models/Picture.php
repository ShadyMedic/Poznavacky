<?php
/** 
 * Třída reprezentující objekt obrázku
 * @author Jan Štěch
 */
class Picture extends DatabaseItem
{
    public const TABLE_NAME = 'obrazky';
    
    public const COLUMN_DICTIONARY = array(
        'id' => 'obrazky_id',
        'src' => 'zdroj',
        'natural' => 'prirodniny_id',
        'enabled' => 'povoleno'
    );
    
    protected const NON_PRIMITIVE_PROPERTIES = array(
        'natural' => Natural::class
    );
    
    protected const DEFAULT_VALUES = array(
        'enabled' => true
    );
    
    protected const CAN_BE_CREATED = true;
    protected const CAN_BE_UPDATED = true;
    
    protected $src;
    protected $natural;
    protected $enabled;
    
    protected $reports;
    
    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * Při nastavení některého z argumentů na undefined, je hodnota dané vlastnosti také nastavena na undefined
     * Při nastavení některého z argumentů na null, není hodnota dané vlastnosti nijak pozměněna
     * @param string|undefined|null $src Adresa, pod kterou lze obrázek najít
     * @param Natural|undefined|null $natural Odkaz na objekt přírodniny, kterou tento obrázek zobrazuje
     * @param bool|undefined|null $enabled TRUE, pokud je obrázek povolen, FALSE, pokud je skryt
     * @param Report[]|undefined|null $reports Pole hlášení tohoto obrázku, jako objekty
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($src = null, $natural = null, $enabled = null, $reports = null)
    {
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($src === null){ $src = $this->src; }
        if ($natural === null){ $natural = $this->natural; }
        if ($enabled === null){ $enabled = $this->enabled; }
        if ($reports === null){ $reports = $this->reports; }
        
        $this->src = $src;
        $this->natural = $natural;
        $this->enabled = $enabled;
        $this->reports = $reports;
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
    
    /**
     * Metoda upravující přírodninu a adresu tohoto obrázku z rozhodnutí administrátora
     * @param Natural $newNatural Objekt reprezentující nově zvolenou přírodninu
     * @param string $newUrl Nová adresa k obrázku
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem nebo jsou zadaná data neplatná
     * @return boolean TRUE, pokud jsou údaje tohoto obrázku úspěšně aktualizovány
     */
    public function updatePicture(Natural $newNatural, string $newUrl)
    {
        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        if (!AccessChecker::checkSystemAdmin())
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
        
        $this->loadIfNotLoaded($this->natural);
        $this->loadIfNotLoaded($this->id);
        
        //Kontrola, zda daná nová URL adresa vede na obrázek a zda je nová přírodnina součástí té samé poznávačky, jako ta stará
        $checker = new PictureAdder($this->natural->getGroup());
        $checker->checkData($newNatural->getName(), $newUrl);  //Pokud nejsou data v pořádku, nastane výjimka a kód nepokračuje
        
        //Kontrola dat OK
        
        //Upravit údaje v databázi
        Db::connect();
        Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET '.self::COLUMN_DICTIONARY['natural'].' = ?, '.self::COLUMN_DICTIONARY['src'].' = ? WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1', array($newNatural->getId(), $newUrl, $this->id));
        
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
        Db::executeQuery('DELETE FROM '.Report::TABLE_NAME.' WHERE '.Report::COLUMN_DICTIONARY['picture'].' = ?', array($this->id));
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
        $result = Db::fetchQuery('SELECT '.Report::COLUMN_DICTIONARY['id'].','.Report::COLUMN_DICTIONARY['reason'].','.Report::COLUMN_DICTIONARY['additionalInformation'].','.Report::COLUMN_DICTIONARY['reportersCount'].' FROM '.Report::TABLE_NAME.' WHERE '.Report::COLUMN_DICTIONARY['picture'].' = ?', array($this->id), true);
        
        if (count($result) === 0)
        {
            //Žádná hlášení tohoto obrázku
            $this->reports = array();
            return;
        }
        
        foreach ($result as $reportInfo)
        {
            //Konstrukce nových objektů hlášení a jejich ukládání do pole
            $report = new Report(false, $reportInfo[Report::COLUMN_DICTIONARY['id']]);
            $report->initialize($this, $reportInfo[Report::COLUMN_DICTIONARY['reason']], $reportInfo[Report::COLUMN_DICTIONARY['additionalInformation']], $reportInfo[Report::COLUMN_DICTIONARY['reportersCount']]);
            $this->reports[] = $report;
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
        Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET '.self::COLUMN_DICTIONARY['enabled'].' = 0 WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1;', array($this->id));
        
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
    	Db::executeQuery('DELETE FROM '.self::TABLE_NAME.' WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1;', array($this->id));
    	$this->id = new undefined();
    	$this->savedInDb = false;
    	return true;
    }
}