<?php
/**
 * Třída reprezentující pozvánku do třídy
 * @author Jan Štěch
 */
class Invitation extends DatabaseItem
{
    public const TABLE_NAME = 'pozvanky';
    
    public const COLUMN_DICTIONARY = array(
        'id' => 'pozvanky_id',
        'user' => 'uzivatele_id',
        'class' => 'tridy_id',
        'expiration' => 'expirace'
    );
    
    private const NON_PRIMITIVE_PROPERTIES = array(
        'user' => User,
        'class' => ClassObject
    );
    
    protected const DEFAULT_VALUES = array(
        /*Všechny vlastnosti musí být vyplněné před uložením do databáze*/
    );
    
    protected const CAN_BE_CREATED = true;
    protected const CAN_BE_UPDATED = true;
    
    public const INVITATION_LIFETIME = 604800;  //7 dní
    
    protected $user;
    protected $class;
    protected $expiration;
    
    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * Při nastavení některého z argumentů na undefined, je hodnota dané vlastnosti také nastavena na undefined
     * Při nastavení některého z argumentů na null, není hodnota dané vlastnosti nijak pozměněna
     * @param User|undefined|null $user Odkaz na objekt uživatele, pro kterého je tato pozvánka určena
     * @param ClassObject|undefined|null $class Odkaz na objekt třídy, do které je možné pomocí této pozvánky získat přístup
     * @param DateTime|undefined|null $expiration Datum a čas, kdy tato pozvánka expiruje
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($user = null, $class = null, $expiration = null)
    {
        //Načtení defaultních hodnot do nenastavených vlastností
        $this->loadDefaultValues();
        
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($user === null){ $user = $this->user; }
        if ($class === null){ $class = $this->class; }
        if ($expiration === null){ $expiration = $this->expiration; }
        
        $this->user = $user;
        $this->class = $class;
        $this->expiration = $expiration;
    }
    
    /**
     * Metoda načítající z databáze uživatele, kterého se tato pozvánka týká, třídu, do které lze pomocí této pozvánky získat přístup a čas, kdy pozvánka expiruje (pokud je známé ID pozvánky)
     * V případě, že není známé ID, ale je známý uživatel, kterého se tato pozvánka týká a třída, do které lze pomocí této pozvánky získat přístup, je načteno ID pozvánky a čas její expirace podle těchto informací
     * @throws BadMethodCallException Pokud se jedná o pozvánku, která dosud není uložena v databázi nebo pokud není o objektu známo dost informací potřebných pro jeho načtení
     * @throws NoDataException Pokud není pozvánka nebo uživatel, kterého se týká nalezena v databázi
     * @return boolean TRUE, pokud jsou vlastnosti této pozvánky úspěšně načteny z databáze
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
            $result = Db::fetchQuery('SELECT '.self::COLUMN_DICTIONARY['user'].', '.self::COLUMN_DICTIONARY['class'].', '.self::COLUMN_DICTIONARY['expiration'].' FROM '.self::TABLE_NAME.' WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1', array($this->id));
            if (empty($result))
            {
                throw new NoDataException(NoDataException::UNKNOWN_INVITATION);
            }
            
            $user = new User(false, $result[self::COLUMN_DICTIONARY['user']]);
            $class = new ClassObject(false, $result[self::COLUMN_DICTIONARY['class']]);
            $expiration = new DateTime($result[self::COLUMN_DICTIONARY['expiration']]);
            
            $this->initialize($user, $class, $expiration);
        }
        else if ($this->isDefined($this->user) && $this->isDefined($this->class))
        {
            $result = Db::fetchQuery('SELECT '.self::COLUMN_DICTIONARY['id'].', '.self::COLUMN_DICTIONARY['expiration'].' FROM '.self::TABLE_NAME.' WHERE '.self::COLUMN_DICTIONARY['user'].' = ? AND '.self::COLUMN_DICTIONARY['class'].' = ? LIMIT 1', array($this->user['id'], $this->class->getId()));
            if (empty($result))
            {
                throw new NoDataException(NoDataException::UNKNOWN_INVITATION);
            }
            $this->id = $result[self::COLUMN_DICTIONARY['id']];
            $this->expiration = new DateTime($result[self::COLUMN_DICTIONARY['expiration']]);
        }
        else
        {
            throw new BadMethodCallException('Not enough properties are know about the item to be able to load the rest');
        }
        return true;
    }
    
    /**
     * Metoda navracející objekt třídy, do které je možné pomocí této pozvánky získat přístup
     * @return ClassObject Objekt třídy, které se týká tato pozvánka
     */
    public function getClass()
    {
        $this->loadIfNotLoaded($this->class);
        return $this->class;
    }
    
    /**
     * Metoda navracející datum (bez času), ve kterém tato pozvánka expiruje
     * @return string Datum expirace této pozvánky ve formátu "den. měsíc. rok" (například 24. 07. 2020)
     */
    public function getExpirationDate()
    {
        $this->loadIfNotLoaded($this->expiration);
        return $this->expiration->format('d. m. Y');
    }
    
    /**
     * Metoda přijímající pozvánku a vytvářející členství v dané třídě pro daného uživatele
     */
    public function accept()
    {
        $this->loadIfNotLoaded($this->class);
        $this->class->addMember($this->user['id']);
    }
    
    /**
     * Metoda odstraňující tuto pozvánku z databáze
     * @return boolean TRUE, pokud je pozvánka úspěšně odstraněna z databáze
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