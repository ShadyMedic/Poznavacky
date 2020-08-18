<?php
/** 
 * Třída uchovávající data o uživateli (ne nutně přihlášeném)
 * @author Jan Štěch
 */
class User extends DatabaseItem implements ArrayAccess
{
    public const TABLE_NAME = 'uzivatele';
    
    protected const DEFAULT_VALUES = array(
        'email' => null,
        'addedPictures' => 0,
        'guessedPictures' => 0,
        'karma' => 0,
    );
    
    const STATUS_GUEST = 'Guest';
    const STATUS_MEMBER = 'Member';
    const STATUS_CLASS_OWNER = 'Class Owner';
    const STATUS_ADMIN = 'Administrator';
    
    protected $id;
    public $name;
    protected $email;
    protected $lastLogin;
    protected $addedPictures;
    protected $guessedPictures;
    protected $karma;
    protected $status;
    
    /**
     * Konstruktor uživatele nastavující jeho ID nebo informaci o tom, že je nový
     * @param bool $isNew FALSE, pokud je již uživatele se zadaným ID nebo později doplněnými informacemi uložen v databázi, TRUE, pokud se jedná o nového uživatele
     * @param int $id ID uživatele v databázi
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
     * @param string|undefined|null $name Přezdívka uživatele
     * @param string|undefined|null $email E-mailová adresa uživatele
     * @param DateTime|undefined|null $lastLogin Datum a čas posledního přihlášení uživatele
     * @param int|undefined|null $addedPictures Počet obrázků přidaných uživatelem
     * @param int|undefined|null $guessedPictures Počet obrázků uhodnutých uživatelem
     * @param int|undefined|null $karma Uživatelova karma
     * @param string|undefined|null $status Uživatelův status
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($name = null, $email = null, $lastLogin = null, $addedPictures = null, $guessedPictures = null, $karma = null, $status = null)
    {
        //Načtení defaultních hodnot do nenastavených vlastností
        $this->loadDefaultValues();
        
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($name === null){ $name = $this->name; }
        if ($email === null){ $email = $this->email; }
        if ($lastLogin === null){ $lastLogin = $this->lastLogin; }
        if ($addedPictures === null){ $addedPictures = $this->addedPictures; }
        if ($guessedPictures === null){ $guessedPictures = $this->guessedPictures; }
        if ($karma === null){ $karma = $this->karma; }
        if ($status === null){ $status = $this->status; }
        
        $this->name = $name;
        $this->email = $email;
        $this->lastLogin = $lastLogin;
        $this->addedPictures = $addedPictures;
        $this->guessedPictures = $guessedPictures;
        $this->karma = $karma;
        $this->status = $status;
    }
    
    /**
     * Metoda načítající z databáze data uživatele (s výjimkou hashe jeho hesla) podle jeho ID (pokud bylo zadáno v konstruktoru)
     * V případě, že není známé ID, ale je známé jméno uživatele, jsou uživatelovo ID a ostatní informace o něm (opět s výjimkou hesla) načteny podle jeho jména
     * @throws BadMethodCallException Pokud se jedná o uživatele, který dosud není uložen v databázi nebo pokud není o objektu známo dost informací potřebných pro jeho načtení
     * @throws NoDataException Pokud není uživatel, který má zadané vlastnosti nalezen
     * @return boolean TRUE, pokud jsou vlastnosti tohoto uživatele úspěšně načteny z databáze
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
            $userData = Db::fetchQuery('SELECT jmeno,email,posledni_prihlaseni,pridane_obrazky,uhodnute_obrazky,karma,status FROM '.self::TABLE_NAME.' WHERE uzivatele_id = ? LIMIT 1', array($this->id));
            if (empty($userData))
            {
                throw new NoDataException(NoDataException::UNKNOWN_USER);
            }
            
            $name = $userData['jmeno'];
            $email = $userData['email'];
            $lastLogin = $userData['posledni_prihlaseni'];
            $addedPictures = $userData['pridane_obrazky'];
            $guessedPictures = $userData['uhodnute_obrazky'];
            $karma = $userData['karma'];
            $status = $userData['status'];
            
            $this->initialize($name, $email, new DateTime($lastLogin), $addedPictures, $guessedPictures, $karma, $status);
        }
        else if ($this->isDefined($this->name))
        {
            $userData = Db::fetchQuery('SELECT uzivatele_id,email,posledni_prihlaseni,pridane_obrazky,uhodnute_obrazky,karma,status FROM '.self::TABLE_NAME.' WHERE jmeno = ? LIMIT 1', array($this->name));
            if (empty($userData))
            {
                throw new NoDataException(NoDataException::UNKNOWN_USER);
            }
            
            $id = $userData['uzivatele_id'];
            $email = $userData['email'];
            $lastLogin = $userData['posledni_prihlaseni'];
            $addedPictures = $userData['pridane_obrazky'];
            $guessedPictures = $userData['uhodnute_obrazky'];
            $karma = $userData['karma'];
            $status = $userData['status'];
            
            $this->id = $id;
            $this->initialize($this->name, $email, $lastLogin, $addedPictures, $guessedPictures, $karma, $status);
        }
      # else if ($this->isDefined($this->email))
      # {
      #     //Implementovat v případě potřeby zkonstruovat objekt uživatele pouze podle jeho e-mailové adresy
      # }
        else
        {
            throw new BadMethodCallException('Not enough properties are know about the item to be able to load the rest');
        }
        return true;
    }
    
    /**
     * Metoda ukládající data tohoto uživatele do databáze
     * Data uživatele se stejným ID jsou v databázy přepsána
     * @throws BadMethodCallException Pokud není známé ID uživatele (znalost ID uživatele je nutná pro modifikaci databázové tabulky) nebo pokud uživatel zatím není uložen v databázi
     * @return boolean TRUE, pokud jsou data uživatele v databázi úspěšně aktualizována
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
            //Aktualizace existujícího uživatele
            $this->loadIfNotAllLoaded();
            
            $result = Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET jmeno = ?, email = ?, posledni_prihlaseni = ?, pridane_obrazky = ?, uhodnute_obrazky = ?, karma = ?, status = ? WHERE uzivatele_id = ? LIMIT 1', array($this->name, $this->email, $this->lastLogin->format('Y-m-d H:i:s'), $this->addedPictures, $this->guessedPictures, $this->karma, $this->status, $this->id));
        }
        else
        {
            //Nelze vytvořit nového uživatele skrz třídu User - lze pouze ve třídě LoggedUser, jelikož musí být známo heslo uživatele
            throw new BadMethodCallException('New user cannot be created from this class, because this class doesn\'t store the user\'s password. Try doing this from the LoggedUser class');
        }
        return $result;
    }
    
    /**
     * Metoda navracející ID tohoto uživatele (pokud není známo, bude načteno z databáze)
     * @return int ID tohoto uživatele nebo FALSE v případě, že tento uživatel ještě není uložen v databázi
     */
    public function getId()
    {
        $this->loadIfNotLoaded($this->id);
        return $this->id;
    }
    
    /**
     * Metoda načítající z databáze aktuální pozvánky pro tohoto uživatele a navracející je jako pole objektů
     * @return Invitation[] Pole aktivních pozvánek jako objekty
     */
    public function getActiveInvitations()
    {
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        $invitationsData = Db::fetchQuery('SELECT pozvanky_id,tridy_id,expirace FROM '.Invitation::TABLE_NAME.' WHERE uzivatele_id = ? AND expirace > NOW()', array($this->id), true);
        if ($invitationsData === false)
        {
            //Žádné pozvánky
            return array();
        }
        
        $invitations = array();
        
        foreach ($invitationsData as $invitationData)
        {
            $invitation = new Invitation(false, $invitationData['pozvanky_id']);
            $invitation->initialize($this, new ClassObject(false, $invitationData['tridy_id']), new DateTime($invitationData['expirace']));
            $invitations[] = $invitation;
        }
        
        return $invitations;
    }
    
    /**
     * Metoda upravující některá data tohoto uživatele z rozhodnutí administrátora
     * @param int $addedPictures Nový počet přidaných obrázků
     * @param int $guessedPictures Nový počet uhodnutých obrázků
     * @param int $karma Nová hodnota karmy
     * @param string $status Nový status uživatele
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem nebo jsou zadaná data neplatná
     * @return boolean TRUE, pokud jsou uživatelova data úspěšně aktualizována
     */
    public function updateAccount(int $addedPictures, int $guessedPictures, int $karma, string $status)
    {
        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        if (!AccessChecker::checkSystemAdmin())
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
        
        //Kontrola platnosti dat
        if ($addedPictures < 0 || $guessedPictures < 0 || !($status === self::STATUS_ADMIN || $status === self::STATUS_CLASS_OWNER || $status === self::STATUS_MEMBER || $status === self::STATUS_GUEST))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ADMINISTRATION_ACCOUNT_UPDATE_INVALID_DATA);
        }
        
        //Kontrola dat OK
        
        $this->addedPictures = $addedPictures;
        $this->guessedPictures = $guessedPictures;
        $this->karma = $karma;
        $this->status = $status;
        
        $this->save();
        
        return true;
    }
    
    /**
     * Metoda odstraňující tento uživatelský účet na základě rozhodnutí administrátora
     * Před samotným odstraněním je provedena kontrola, zda je možné uživatele odstranit
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem nebo pokud odstraňovaný uživatel spravuje nějakou třídu
     * @return boolean TRUE, pokud je uživatel úspěšně odstraněn z databáze
     */
    public function deleteAccountAsAdmin()
    {
        $this->loadIfNotLoaded($this->id);
        
        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        if (!AccessChecker::checkSystemAdmin())
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
        
        //Kontrola, zda uživatel není správcem žádné třídy
        Db::connect();
        $administratedClasses = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM tridy WHERE spravce = ? LIMIT 1', array($this->id));
        if ($administratedClasses['cnt'] > 0)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ADMINISTRATION_ACCOUNT_DELETION_ADMINISTRATOR);
        }
        
        //Kontrola dat OK
        
        $this->delete();
        
        return true;
    }
    
    /**
     * Metoda odstraňující tohoto uživatele
     * @return boolean TRUE, pokud je uživatel úspěšně odstraněn z databáze
     * {@inheritDoc}
     * @see DatabaseItem::delete()
     */
    public function delete()
    {
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        Db::executeQuery('DELETE FROM '.self::TABLE_NAME.' WHERE uzivatele_id = ? LIMIT 1;', array($this->id));
        $this->id = new undefined();
        $this->savedInDb = false;
        return true;
    }
    
    /**
     * Metoda pro zjišťování existence některé vlastnosti uživatele
     * {@inheritDoc}
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        $this->loadIfNotLoaded($this->$offset);
        return (isset($this->$offset));
    }
    
    /**
     * Metoda pro získání hodnoty nějaké z vlastností uživatele
     * {@inheritDoc}
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        $this->loadIfNotLoaded($this->$offset);
        return $this->$offset;
    }
    
    /**
     * Metoda pro nastavení hodnoty nějaké z vlastností uživatele
     * 
     * Nelze použít pro nastavení hodnoty id
     * {@inheritDoc}
     * @see ArrayAccess::offsetSet()
     * @throws BadMethodCallException Při pokusu změnit ID
     */
    public function offsetSet($offset, $value)
    {
        if ($offset !== 'id')
        {
            $this->offset = $value;
        }
        else
        {
            throw new BadMethodCallException('It isn\'t allowed to edit user\'s ID.');
        }
    }
    
    /**
     * Metoda pro odebrání hodnoty nějaké z vlastností uživatele
     * 
     * Nelze použít pro odebrání jakékoliv vlastnosti
     * {@inheritDoc}
     * @see ArrayAccess::offsetUnset()
     * @throws BadMethodCallException Při pokusu odebrat jakoukoli vlastnost
     */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException('It isn\'t allowed to remove user\'s properities.');
    }
}