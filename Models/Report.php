<?php
/** 
 * Třída reprezentující hlášení obrázku
 * @author Jan Štěch
 */
class Report extends DatabaseItem
{
    public const TABLE_NAME = 'hlaseni';
    
    public const COLUMN_DICTIONARY = array(
        'id' => 'hlaseni_id',
        'picture' => 'obrazky_id',
        'reason' => 'duvod',
        'additionalInformation' => 'dalsi_informace',
        'reportersCount' => 'pocet'
    );
    
    protected const DEFAULT_VALUES = array(
        'additionalInformation' => null,
        'reportersCount' => 1
    );
    
    protected const CAN_BE_CREATED = true;
    protected const CAN_BE_UPDATED = true;
    
    const ALL_REASONS = array(self::REASON_NOT_DISPLAYING, self::REASON_LONG_LOADING, self::REASON_INCORRECT_NATURAL, self::REASON_CONTAINS_NAME, self::REASON_BAD_RESOLUTION, self::REASON_COPYRIGHT, self::REASON_OTHER, self::REASON_OTHER_ADMIN);
    const ADMIN_REQUIRING_REASONS = array(self::REASON_COPYRIGHT, self::REASON_OTHER_ADMIN);
    const LONG_LOADING_AVAILABLE_DELAYS = array('>2 s', '>5 s', '>10 s', '>20 s');
    const INCORRECT_NATURAL_DEFAULT_INFO = 'Nezadáno';
    
    const REASON_NOT_DISPLAYING = 'Obrázek se nezobrazuje správně';
    const REASON_LONG_LOADING = 'Obrázek se načítá příliš dlouho';
    const REASON_INCORRECT_NATURAL = 'Obrázek zobrazuje nesprávnou přírodninu';
    const REASON_CONTAINS_NAME = 'Obrázek obsahuje název přírodniny';
    const REASON_BAD_RESOLUTION = 'Obrázek má příliš špatné rozlišení';
    const REASON_COPYRIGHT = 'Obrázek porušuje autorská práva';
    const REASON_OTHER = 'Jiný důvod (řeší správce třídy)';
    const REASON_OTHER_ADMIN = 'Jiný důvod (řeší správce služby)';
    
    protected $picture;
    protected $reason;
    protected $additionalInformation;
    protected $reportersCount;
    
    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * Při nastavení některého z argumentů na undefined, je hodnota dané vlastnosti také nastavena na undefined
     * Při nastavení některého z argumentů na null, není hodnota dané vlastnosti nijak pozměněna
     * @param Picture|undefined|null $picture Odkaz na objekt obrázku, ke kterému se toto hlášení vztahuje
     * @param string|undefined|null $reason Důvod hlášení (musí být jedna z konstant této třídy začínající "REASON_")
     * @param string|undefined|null $additionalInformation Další informace o hlášení odeslané uživatelem
     * @param int|undefined|null $reportersCount Počet uživatelů, kteří odeslali hlášení tohoto typu
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($picture = null, $reason = null, $additionalInformation = null, $reportersCount = null)
    {
        //Načtení defaultních hodnot do nenastavených vlastností
        $this->loadDefaultValues();
        
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($picture === null){ $picture = $this->picture; }
        if ($reason === null){ $reason = $this->reason; }
        if ($additionalInformation === null){ $additionalInformation = $this->additionalInformation; }
        if ($reportersCount === null){ $reportersCount = $this->reportersCount; }
        
        $this->picture = $picture;
        $this->reason = $reason;
        $this->additionalInformation = $additionalInformation;
        $this->reportersCount = $reportersCount;
    }
    
    /**
     * Metoda načítající z databáze všechny vlastnosti objektu podle ID (pokud je vyplněno)
     * Pokud není ID vyplněno, jsou vlastnosti (včetně ID) načteny podle obrázku, ke kterému se toto hlášení vztahuje, důvodu a dalších informací odeslaných uživatelem
     * @throws BadMethodCallException Pokud se jedná o hlášení, které dosud není uloženo v databázi nebo pokud není o objektu známo dost informací potřebných pro jeho načtení
     * @throws NoDataException Pokud není hlášení, s daným ID nebo názvem nalezeno v databázi
     * @return boolean TRUE, pokud jsou vlastnosti tohoto hlášení úspěšně načteny z databáze
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
            $result = Db::fetchQuery('SELECT '.self::COLUMN_DICTIONARY['picture'].','.self::COLUMN_DICTIONARY['reason'].','.self::COLUMN_DICTIONARY['additionalInformation'].','.self::COLUMN_DICTIONARY['reportersCount'].' FROM '.self::TABLE_NAME.' WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1', array($this->id));
            if (empty($result))
            {
                throw new NoDataException(NoDataException::UNKNOWN_REPORT);
            }
            
            $picture = new Picture(false, $result[self::COLUMN_DICTIONARY['picture']]);
            $reason = $result[self::COLUMN_DICTIONARY['reason']];
            $additionalInformation = $result[self::COLUMN_DICTIONARY['additionalInformation']];
        }
        else if ($this->isDefined($this->picture) && $this->isDefined($this->reason) && $this->isDefined($this->additionalInformation))
        {
            $result = Db::fetchQuery('SELECT '.self::COLUMN_DICTIONARY['id'].','.self::COLUMN_DICTIONARY['reportersCount'].' FROM '.self::TABLE_NAME.' WHERE '.self::COLUMN_DICTIONARY['picture'].' = ? AND '.self::COLUMN_DICTIONARY['reason'].' = ? AND '.self::COLUMN_DICTIONARY['additionalInformation'].' = ? LIMIT 1', array($this->picture->getId(), $this->reason, $this->additionalInformation));
            if (empty($result))
            {
                throw new NoDataException(NoDataException::UNKNOWN_REPORT);
            }
            
            $this->id = $result[self::COLUMN_DICTIONARY['id']];
            $picture = null;
            $reason = null;
            $additionalInformation = null;
        }
        else
        {
            throw new BadMethodCallException('Not enough properties are know about the item to be able to load the rest');
        }
        
        $reportersCount = $result[self::COLUMN_DICTIONARY['reportersCount']];
        
        $this->initialize($picture, $reason, $additionalInformation, $reportersCount);
        
        return true;
    }
    
    /**
     * Metoda ukládající data tohoto hlášení do databáze
     * Pokud se jedná o nové hlášení (vlastnost $savedInDb je nastavena na FALSE), je vložen nový záznam
     * V opačném případě jsou přepsána data hlášení se stejným ID
     * @throws BadMethodCallException Pokud se nejedná o nové hlášení a zároveň není známo jeho ID (znalost ID hlášení je nutné pro modifikaci databázové tabulky)
     * @return boolean TRUE, pokud je hlášení úspěšně uloženo do databáze
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
            //Aktualizace existujícího hlášení
            $this->loadIfNotAllLoaded();
            
            $result = Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET '.self::COLUMN_DICTIONARY['picture'].' = ?, '.self::COLUMN_DICTIONARY['reason'].' = ?, '.self::COLUMN_DICTIONARY['additionalInformation'].' = ?, '.self::COLUMN_DICTIONARY['reportersCount'].' = ? WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1', array($this->picture->getId(), $this->reason, $this->additionalInformation, $this->reportersCount, $this->id));
        }
        else
        {
            //Tvorba nového hlášení
            $this->id = Db::executeQuery('INSERT INTO '.self::TABLE_NAME.' ('.self::COLUMN_DICTIONARY['picture'].','.self::COLUMN_DICTIONARY['reason'].','.self::COLUMN_DICTIONARY['additionalInformation'].','.self::COLUMN_DICTIONARY['reportersCount'].') VALUES (?,?,?,?)', array($this->picture->getId(), $this->reason, $this->additionalInformation, $this->reportersCount), true);
            if (!empty($this->id))
            {
                $this->savedInDb = true;
                $result = true;
            }
        }
        return $result;
    }
    
    /**
     * Metoda navracející objekt nahlášeného obrázku
     * @return Picture Nahlášený obrázek
     */
    public function getPicture()
    {
        $this->loadIfNotLoaded($this->picture);
        return $this->picture;
    }
    
    /**
     * Metoda navracející ID nahlášeného obrázku
     * @return int ID obrázku
     */
    public function getPictureId()
    {
        $this->loadIfNotLoaded($this->picture);
        return $this->picture->getId();
    }
    
    /**
     * Metoda navracející URL nahlášeného obrázku
     * @return string Zdroj obrázku
     */
    public function getUrl()
    {
        $this->loadIfNotLoaded($this->picture);
        return $this->picture->getSrc();
    }
    
    /**
     * Metoda navracející řetězec se zařazením obrázku ve formátu <Název třídy> / <Název poznávačky> / <Název části> / <Název přírodniny>
     * @return string Řetězec obsahující cestu k obrázku
     */
    public function getPicturePath()
    {
        $this->loadIfNotLoaded($this->picture);
        $natural = $this->picture->getNatural();
        $part = $natural->getPart();
        $group = $part->getGroup();
        $class = $group->getClass();
        return $class->getName().' / '.$group->getName().' / '.$part->getName();
    }
    
    /**
     * Metoda navracející objekt části, do které patří nahlášený obrázek
     * @return Part Objekt části, do které nahlášený obrázek patří
     */
    public function getPartWithPicture()
    {
        $this->loadIfNotLoaded($this->picture);
        $natural = $this->picture->getNatural();
        return $natural->getPart();
    }
    
    /**
     * Metoda navracející název přírodniny, ke které byl nahlášený obrázek nahrán
     * @return string Název přírodniny na obrázku
     */
    public function getNaturalName()
    {
        $this->loadIfNotLoaded($this->picture);
        $natural = $this->picture->getNatural();
        return $natural->getName();
    }
    
    /**
     * Metoda navracející důvod hlášení
     * @return string Důvod hlášení (měl by být jednou z konstant této třídy)
     */
    public function getReason()
    {
        $this->loadIfNotLoaded($this->reason);
        return $this->reason;
    }
    
    /**
     * Metoda navracející další informace o hlášení
     * @return string Další informace o hlášení (pokud žádné nebyly poskytnuty, tak prázdný řetězec)
     */
    public function getAdditionalInformation()
    {
        $this->loadIfNotLoaded($this->additionalInformation);
        return $this->additionalInformation;
    }
    
    /**
     * Metoda navracející počet hlášení stejného typu
     * @return int Počet hlášení
     */
    public function getReportersCount()
    {
        $this->loadIfNotLoaded($this->reportersCount);
        return $this->reportersCount;
    }
    
    /**
     * Metoda zvyšující počet hlášení tohoto typu o 1
     */
    public function increaseReportersCount()
    {
        $this->loadIfNotLoaded($this->reportersCount);
        $this->reportersCount++;
    }
    
    /**
     * Metoda odstraňující toto hlášení z databáze
     * @return boolean TRUE, pokud je hlášení úspěšně odstraněno z databáze
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