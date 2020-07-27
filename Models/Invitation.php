<?php
/**
 * Třída reprezentující pozvánku do třídy
 * @author Jan Štěch
 */
class Invitation extends DatabaseItem
{
    public const INVITATION_LIFETIME = 604800;  //7 dní
    
    private $user;
    private $class;
    private $expiration;
    
    /**
     * Konstruktor pozvánky nastavující její ID nebo informaci o tom, že je nová
     * @param bool $isNew FALSE, pokud je již pozvánka se zadaným ID nebo později doplněnými informacemi uložena v databázi, TRUE, pokud se jedná o novou pozvánku
     * @param int $id ID pozvánky (možné pouze pokud je první argument FALSE; pokud není vyplněno, bude načteno z databáze po vyplnění dalších údajů o ní pomocí metody Invitation::initialize())
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function __construct(bool $isNew, int $id = 0)
    {
        parent::__construct($isNew, $id);
        }
    
    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * @param User $user Odkaz na objekt uživatele, pro kterého je tato pozvánka určena
     * @param ClassObject $class Odkaz na objekt třídy, do které je možné pomocí této pozvánky získat přístup
     * @param DateTime $expiration Datum a čas, kdy tato pozvánka expiruje
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize(User $user = null, ClassObject $class = null, DateTime $expiration = null)
    {
        $this->user = $user;
        $this->class = $class;
        $this->expiration = $expiration;
    }
    
    /**
     * Metoda načítající z databáze uživatele, kterého se tato pozvánka týká, třídu, do které lze pomocí této pozvánky získat přístup a čas, kdy pozvánka expiruje (pokud je známé ID pozvánky)
     * V případě, že není známé ID, ale je známý uživatel, kterého se tato pozvánka týký a třída, do které lze pomocí této pozvánky získat přístup, je načteno ID pozvánky a čas její expirace
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
        
        if (isset($this->id))
        {
            $result = Db::fetchQuery('SELECT uzivatele_id, tridy_id, expirace FROM pozvanky WHERE pozvanky_id = ? LIMIT 1', array($this->id));
            if (empty($result))
            {
                throw new NoDataException(NoDataException::UNKNOWN_INVITATION);
            }
            
            $userData = Db::fetchQuery('SELECT jmeno,email,posledni_prihlaseni,pridane_obrazky,uhodnute_obrazky,karma,status FROM uzivatele WHERE uzivatele_id = ? LIMIT 1', array($result['uzivatele_id']));
            if (empty($userData))
            {
                throw new NoDataException(NoDataException::UNKNOWN_USER);
            }
            $user = new User($result['uzivatele_id'], $userData['jmeno'], $userData['email'], $userData['pridane_obrazky'], $userData['uhodnute_obrazky'], $userData['karma'], $userData['status']);
            $class = new ClassObject($result['tridy_id']);
            $expiration = new DateTime($result['expirace']);
            
            $this->initialize($user, $class, $expiration);
        }
        else if (isset($this->user) && isset($this->class))
        {
            $result = Db::fetchQuery('SELECT pozvanky_id, expirace FROM pozvanky WHERE uzivatele_id = ? AND tridy_id = ? LIMIT 1', array($this->user['id'], $this->class->getId()));
            if (empty($result))
            {
                throw new NoDataException(NoDataException::UNKNOWN_INVITATION);
            }
            $this->id = $result['pozvanky_id'];
            $this->expiration = new DateTime($result['expirace']);
        }
        else
        {
            throw new BadMethodCallException('Not enough properties are know about the item to be able to load the rest');
        }
        return true;
    }
    
    /**
     * Metoda ukládající data této pozvánky do databáze
     * Pokud se jedná o novou pozvánku (vlastnost $savedInDb je nastavena na FALSE), je vložen nový záznam
     * V opačném případě jsou přepsána data pozvánky se stejným ID
     * @throws BadMethodCallException Pokud se nejedná o novou pozvánku a zároveň není známo jeho ID (znalost ID pozvánky je nutná pro modifikaci databázové tabulky)
     * @return boolean TRUE, pokud je pozvánka úspěšně uložena do databáze
     * {@inheritDoc}
     * @see DatabaseItem::save()
     */
    public function save()
    {
        if ($this->savedInDb === true && empty($this->id))
        {
            throw new BadMethodCallException('ID of the item must be loaded before saving into the database, since this item isn\'t new');
        }
        
        Db::connect();
        if ($this->savedInDb)
        {
            //Aktualizace existující pozvánky
            $this->id = Db::executeQuery('UPDATE pozvanky SET uzivatele_id = ?, tridy_id = ?, expirace = ? WHERE pozvanky_id = ? LIMIT 1', array($this->user['id'], $this->class->getId(), $this->expiration->format('Y-m-d H:i:s'), $this->id));
        }
        else
        {
            //Tvorba nové pozvánky
            $this->id = Db::executeQuery('INSERT INTO pozvanky (uzivatele_id,tridy_id,expirace) VALUES (?,?,?)', array($this->user['id'], $this->class->getId(), $this->expiration->format('Y-m-d H:i:s')), true);
        }
        return true;
    }
    
    /**
     * Metoda navracející ID této pozvánky
     * @return int ID této pozvánky (v případě neznámého ID je navrácena hodnota 0)
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Metoda navracející objekt třídy, do které je možné pomocí této pozvánky získat přístup
     * @return ClassObject Objekt třídy, které se týká tato pozvánka
     */
    public function getClass()
    {
        return $this->class;
    }
    
    /**
     * Metoda navracející datum (bez času), ve kterém tato pozvánka expiruje
     * @return string Datum expirace této pozvánky ve formátu "den. měsíc. rok" (například 24. 07. 2020)
     */
    public function getExpirationDate()
    {
        return $this->expiration->format('d. m. Y');
    }
    
    /**
     * Metoda přijímající pozvánku a vytvářející členství v dané třídě pro daného uživatele
     */
    public function accept()
    {
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
        Db::connect();
        Db::executeQuery('DELETE FROM pozvanky WHERE pozvanky_id = ? LIMIT 1;', array($this->id));
        unset($this->id);
        $this->savedInDb = false;
        return true;
    }
}