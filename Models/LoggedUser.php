<?php
/** 
 * Třída uchovávající data o právě přihlášeném uživateli
 * @author Jan Štěch
 */
class LoggedUser extends User
{
    protected const DEFAULT_VALUES = array(
        'email' => null,
        'lastChangelog' => 0,
        'lastLevel' => 0,
        'lastFolder' => null,
        'theme' => 0,
        'addedPictures' => 0,
        'guessedPictures' => 0,
        'karma' => 0,
        'status' => self::STATUS_MEMBER,
    );
    
    protected $hash;
    protected $lastChangelog;
    protected $lastLevel;
    protected $lastFolder;
    protected $theme;
    
    /**
     * Konstruktor uživatele nastavující jeho ID nebo informaci o tom, že je nový
     * @param bool $isNew FALSE, pokud je již uživatele se zadaným ID nebo později doplněnými informacemi uložen v databázi, TRUE, pokud se jedná o nového uživatele
     * @param int $id ID uživatele v databázi
     * {@inheritDoc}
     * @see User::initialize()
     */
    public function __construct(bool $isNew, int $id = 0)
    {
        parent::__construct($isNew, $id);
    }
    
    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * @param string $name Přezdívka uživatele
     * @param string|null $email E-mailová adresa uživatele
     * @param DateTime $lastLogin Datum a čas posledního přihlášení uživatele
     * @param int $addedPictures Počet obrázků přidaných uživatelem
     * @param int $guessedPictures Počet obrázků uhodnutých uživatelem
     * @param int $karma Uživatelova karma
     * @param string $status Uživatelův status
     * @param string $hash Heš uživatelova hesla z databáze
     * @param float $lastChangelog Poslední zobrazený changelog
     * @param int $lastLevel Poslední navštívěná úroveň složek na menu stránce
     * @param int|null $lastFolder Poslední navštívená složka na menu stránce v určité úrovni
     * @param int $theme Zvolený vzhled stránek
     * {@inheritDoc}
     * @see User::initialize()
     */
    public function initialize(string $name = '', $email = '', DateTime $lastLogin = null, int $addedPictures = -1, int $guessedPictures = -1, int $karma = -1, string $status = '', string $hash = '', float $lastChangelog = -1.0, int $lastLevel = -1, $lastFolder = -1, int $theme = -1)
    {
        //Načtení defaultních hodnot do nenastavených vlastností
        $this->loadDefaultValues();
        
        //Nastav vlastnosti zděděné z mateřské třídy
        parent::initialize($name, $email, $lastLogin, $addedPictures, $guessedPictures, $karma, $status);
        
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($hash === ''){ $hash = $this->hash; }
        if ($lastChangelog === -1.0){ $lastChangelog = $this->lastChangelog; }
        if ($lastLevel === -1){ $lastLevel = $this->lastLevel; }
        if ($lastFolder === -1){ $lastFolder = $this->lastFolder; }
        if ($theme === -1){ $theme = $this->theme; }
        
        $this->hash = $hash;
        $this->lastChangelog = $lastChangelog;
        $this->lastLevel = $lastLevel;
        $this->lastFolder = $lastFolder;
        $this->theme = $theme;
    }
    
    /**
     * Metoda načítající z databáze data přihlášeného uživatele podle jeho ID (pokud bylo zadáno v konstruktoru)
     * V případě, že není známé ID, ale je známé jméno přihlášeného uživatele, jsou uživatelovo ID a ostatní informace o něm načteny podle jeho jména
     * @throws BadMethodCallException Pokud se jedná o uživatele, který dosud není uložen v databázi nebo pokud není o objektu známo dost informací potřebných pro jeho načtení
     * @throws NoDataException Pokud není uživatel, který má zadané vlastnosti nalezen
     * @return boolean TRUE, pokud jsou vlastnosti tohoto uživatele úspěšně načteny z databáze
     * {@inheritDoc}
     * @see User::load()
     */
    public function load()
    {
        $this->loadDefaultValues();
        
        if ($this->savedInDb === false)
        {
            throw new BadMethodCallException('Cannot load data about an item that is\'t saved in the database yet');
        }
        
        Db::connect();
        
        if (isset($this->id))
        {
            $userData = Db::fetchQuery('SELECT jmeno,heslo,email,posledni_prihlaseni,posledni_changelog,posledni_uroven,posledni_slozka,vzhled,pridane_obrazky,uhodnute_obrazky,karma,status FROM '.self::TABLE_NAME.' WHERE uzivatele_id = ? LIMIT 1', array($this->id));
            if (empty($userData))
            {
                throw new NoDataException(NoDataException::UNKNOWN_USER);
            }
            
            $name = $userData['jmeno'];
            $hash = $userData['heslo'];
            $email = $userData['email'];
            $lastLogin = $userData['posledni_prihlaseni'];
            $lastChangelog = $userData['posledni_changelog'];
            $lastLevel = $userData['posledni_uroven'];
            $lastFolder = $userData['posledni_slozka'];
            $theme = $userData['vzhled'];
            $addedPictures = $userData['pridane_obrazky'];
            $guessedPictures = $userData['uhodnute_obrazky'];
            $karma = $userData['karma'];
            $status = $userData['status'];
            
            $this->initialize($name, $email, new DateTime($lastLogin), $addedPictures, $guessedPictures, $karma, $status, $hash, $lastChangelog, $lastLevel, $lastFolder, $theme);
        }
        else if (isset($this->name))
        {
            $userData = Db::fetchQuery('SELECT uzivatele_id,heslo,email,posledni_prihlaseni,posledni_changelog,posledni_uroven,posledni_slozka,vzhled,pridane_obrazky,uhodnute_obrazky,karma,status FROM '.self::TABLE_NAME.' WHERE jmeno = ? LIMIT 1', array($this->name));
            if (empty($userData))
            {
                throw new NoDataException(NoDataException::UNKNOWN_USER);
            }
            
            $id = $userData['uzivatele_id'];
            $hash = $userData['heslo'];
            $email = $userData['email'];
            $lastLogin = $userData['posledni_prihlaseni'];
            $lastChangelog = $userData['posledni_changelog'];
            $lastLevel = $userData['posledni_uroven'];
            $lastFolder = $userData['posledni_slozka'];
            $theme = $userData['vzhled'];
            $addedPictures = $userData['pridane_obrazky'];
            $guessedPictures = $userData['uhodnute_obrazky'];
            $karma = $userData['karma'];
            $status = $userData['status'];
            
            $this->id = $id;
            $this->initialize($this->name, $email, new DateTime($lastLogin), $addedPictures, $guessedPictures, $karma, $status, $hash, $lastChangelog, $lastLevel, $lastFolder, $theme);
        }
      # else if (isset($this->email))
      # {
      #     //Implementovat v případě potřeby zkonstruovat objekt přihlášeného uživatele pouze podle jeho e-mailové adresy
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
     * @throws BadMethodCallException Pokud není známé ID uživatele (znalost ID uživatele je nutná pro modifikaci databázové tabulky)
     * @return boolean TRUE, pokud jsou data uživatele v databázi úspěšně aktualizována nebo pokud je vytvořen nový uživatelský účet
     * {@inheritDoc}
     * @see User::save()
     */
    public function save()
    {
        $this->loadDefaultValues();
        
        if ($this->savedInDb === true && empty($this->id))
        {
            throw new BadMethodCallException('ID of the item must be loaded before saving into the database, since this item isn\'t new');
        }
        
        Db::connect();
        if ($this->savedInDb)
        {
            //Aktualizace existujícího uživatele
            $result = Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET jmeno = ?, heslo = ?, email = ?, posledni_prihlaseni = ?, posledni_changelog = ?, posledni_uroven = ?, posledni_složka = ?, vzhled = ?, pridane_obrazky = ?, uhodnute_obrazky = ?, karma = ?, status = ? WHERE uzivatele_id = ? LIMIT 1', array($this->name, $this->hash, $this->email, $this->lastLogin->format('Y-m-d H:i:s'), $this->lastChangelog, $this->lastLevel, $this->lastFolder, $this->theme, $this->addedPictures, $this->guessedPictures, $this->karma, $this->status, $this->id));
        }
        else
        {
            //Tvorba nového uživatele
            $this->id = Db::executeQuery('INSERT INTO '.self::TABLE_NAME.' (jmeno,heslo,email,posledni_prihlaseni,posledni_changelog,posledni_uroven,posledni_slozka,vzhled,pridane_obrazky,uhodnute_obrazky,karma,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)', array($this->name, $this->hash, $this->email, $this->lastLogin, $this->lastChangelog, $this->lastLevel, $this->lastFolder, $this->theme, $this->addedPictures, $this->guessedPictures, $this->karma, $this->status), true);
            if (!empty($this->id))
            {
                $this->savedInDb = true;
                $result = true;
            }
        }
        return $result;
    }
    
    /**
     * Metoda ukládající do databáze nový požadavek na změnu jména od přihlášeného uživatele, pokud žádný takový požadavek neexistuje nebo aktualizující stávající požadavek
     * Data jsou předem ověřena
     * @param string $newName Požadované nové jméno
     * @throws AccessDeniedException Pokud jméno nevyhovuje podmínkám systému
     * @return boolean TRUE, pokud je žádost úspěšně vytvořena/aktualizována
     */
    public function requestNameChange(string $newName)
    {
        if (mb_strlen($newName) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_NAME_CHANGE_NO_NAME);}
        
        //Kontrola délky jména
        $validator = new DataValidator();
        try
        {
            $validator->checkLength($newName, 4, 15, 0);
        }
        catch(RangeException $e)
        {
            if ($e->getMessage() === 'long')
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_NAME_CHANGE_NAME_TOO_LONG, null, $e);
            }
            else if ($e->getMessage() === 'short')
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_NAME_CHANGE_NAME_TOO_SHORT, null, $e);
            }
        }
        
        //Kontrola znaků ve jméně
        try
        {
            $validator->checkCharacters($newName, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ ', 0);
        }
        catch (InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_NAME_CHANGE_INVALID_CHARACTERS, null, $e);
        }
        
        //Kontrola dostupnosti jména
        try
        {
            $validator->checkUniqueness($newName, 0);
        }
        catch (InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_NAME_CHANGE_DUPLICATE_NAME, null, $e);
        }
        
        //Kontrola dat OK
        
        //Zkontrolovat, zda již existuje žádost o změnu jména od přihlášeného uživatele
        $applications = Db::fetchQuery('SELECT zadosti_jmena_uzivatele_id FROM zadosti_jmena_uzivatele WHERE uzivatele_id = ? LIMIT 1', array(UserManager::getId()));
        if (!empty($applications['zadosti_jmena_uzivatele_id']))
        {
            //Přepsání existující žádosti
            Db::executeQuery('UPDATE zadosti_jmena_uzivatele SET nove = ?, cas = NOW() WHERE zadosti_jmena_uzivatele_id = ? LIMIT 1', array($newName, $applications['zadosti_jmena_uzivatele_id']));
        }
        else
        {
            //Uložení nové žádosti
            Db::executeQuery('INSERT INTO zadosti_jmena_uzivatele (uzivatele_id,nove,cas) VALUES (?,?,NOW())', array($this->id, $newName));
        }
        return true;
    }
    
    /**
     * Metoda ověřující heslo přihlášeného uživatele a v případě úspěchu měnící jeho heslo
     * Všechna data jsou předem ověřena
     * @param string $oldPassword Stávající heslo pro ověření
     * @param string $newPassword Nové heslo
     * @param string $newPasswordAgain Opsané nové heslo
     * @throws AccessDeniedException Pokud některý z údajů nesplňuje podmínky systému
     * @return boolean TRUE, pokud je heslo úspěšně změněno
     */
    public function changePassword(string $oldPassword, string $newPassword, string $newPasswordAgain)
    {
        if (mb_strlen($oldPassword) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_NO_OLD_PASSWORD);}
        if (mb_strlen($newPassword) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_NO_PASSWORD);}
        if (mb_strlen($newPasswordAgain) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_NO_REPEATED_PASSWORD);}
        
        //Kontrola hesla
        if (!AccessChecker::recheckPassword($oldPassword))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_WRONG_PASSWORD);
        }
        
        //Kontrola délky nového hesla
        $validator = new DataValidator();
        try
        {
            $validator->checkLength($newPassword, 6, 31, 1);
        }
        catch (RangeException $e)
        {
            if ($e->getMessage() === 'long')
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_TOO_LONG, null, $e);
            }
            else if ($e->getMessage() === 'short')
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_TOO_SHORT, null, $e);
            }
        }
        
        //Kontrola znaků v novém hesle
        try
        {
            $validator->checkCharacters($newPassword, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ {}()[]#:;^,.?!|_`~@$%/+-*=\"\'', 1);
        }
        catch(InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_INVALID_CHARACTERS, null, $e);
        }
        //Kontrola shodnosti hesel
        if ($newPassword !== $newPasswordAgain)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_DIFFERENT_PASSWORDS);
        }
        
        //Kontrola dat OK
        
        //Aktualizovat heslo v databázi
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        Db::connect();
        Db::executeQuery('UPDATE uzivatele SET heslo = ? WHERE uzivatele_id = ? LIMIT 1', array($hashedPassword, UserManager::getId()));
        $this->hash = $hashedPassword;
        return true;
    }
    
    /**
     * Metoda ověřující heslo uživatele a v případě úspěchu měnící e-mailovou adresu přihlášeného uživatele v databázi
     * Data jsou předtím ověřena
     * @param string $password Heslo přihlášeného uživatele pro ověření
     * @param string $newEmail Nový e-mail
     * @throws AccessDeniedException Pokud některý z údajů nesplňuje podmínky systému
     * @return boolean TRUE, pokud je e-mail úspěšně změněn
     */
    public function changeEmail(string $password, string $newEmail)
    {
        if (mb_strlen($password) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_EMAIL_CHANGE_NO_PASSWORD);}
        if (mb_strlen($newEmail) === 0){$newEmail = NULL;}
        
        //Kontrola hesla
        if (!AccessChecker::recheckPassword($password))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_EMAIL_CHANGE_WRONG_PASSWORD);
        }
        
        //Kontrola délky a unikátnosti e-mailu (pokud ho uživatel nechce odstranit)
        if (!empty($newEmail))
        {
            $validator = new DataValidator();
            try
            {
                $validator->checkLength($newEmail, 0, 255, 2);
                $validator->checkUniqueness($newEmail, 2);
            }
            catch (RangeException $e)
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_EMAIL_CHANGE_EMAIL_TOO_LONG, null, $e);
            }
            catch (InvalidArgumentException $e)
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_EMAIL_CHANGE_DUPLICATE_EMAIL, null, $e);
            }
            
            //Kontrola platnosti e-mailu
            if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL) && !empty($newEmail))
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_INVALID_EMAIL);
            }
        }
        
        //Kontrola dat OK
        
        //Aktualizovat databázi
        Db::connect();
        Db::executeQuery('UPDATE uzivatele SET email = ? WHERE uzivatele_id = ? LIMIT 1', array($newEmail, UserManager::getId()));
        $this->email = $newEmail;
        return true;
    }
    
    /**
     * Metoda přidávající uživateli jak v $_SESSION tak v databázi jeden bod v poli přidaných obrázků
     * @return boolean TRUE, pokud vše proběhne hladce
     */
    public function incrementAddedPictures()
    {
        $this->addedPictures++;
        Db::connect();
        return Db::executeQuery('UPDATE uzivatele SET pridane_obrazky = (pridane_obrazky + 1) WHERE uzivatele_id = ?', array($this->id));
    }
    
    /**
     * Metoda přidávající uživateli jak v $_SESSION tak v databázi jeden bod v poli uhodnutých obrázků
     * @return boolean TRUE, pokud vše proběhne hladce
     */
    public function incrementGuessedPictures()
    {
        $this->guessedPictures++;
        Db::connect();
        return Db::executeQuery('UPDATE uzivatele SET uhodnute_obrazky = (uhodnute_obrazky + 1) WHERE uzivatele_id = ?', array($this->id));
    }
    
    /**
     * Metoda ověřující heslo přihlášeného uživatele a v případě úspěchu odstraňující jeho uživatelský účet
     * Po odstranění z databáze jsou uživatelova data vymazána i ze $_SESSION
     * Data z vlastností této instance jsou vynulována
     * Instance, na které je tato metoda provedena by měla být ihned zničena pomocí unset()
     * @param string $password Heslo přihlášeného uživatele pro ověření
     * @throws AccessDeniedException Pokud není heslo správné, vyplněné nebo uživatel nemůže smazat svůj účet
     * @return boolean TRUE, pokud je uživatel úspěšně odstraněn z databáze a odhlášen
     */
    public function deleteAccount(string $password)
    {
        if (mb_strlen($password) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_ACCOUNT_DELETION_NO_PASSWORD);}
        
        //Kontrola hesla
        if (!AccessChecker::recheckPassword($password))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ACCOUNT_DELETION_WRONG_PASSWORD);
        }
        
        //Kontrola, zda uživatel není správcem žádné třídy
        Db::connect();
        $administratedClasses = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM tridy WHERE spravce = ? LIMIT 1', array(UserManager::getId()));
        if ($administratedClasses['cnt'] > 0)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ACCOUNT_DELETION_CLASS_ADMINISTRATOR);
        }
        
        //Kontrola dat OK
        
        //Odstranit uživatele z databáze
        Db::executeQuery('DELETE FROM uzivatele WHERE uzivatele_id = ?', array(UserManager::getId()));
        
        //Odhlásit uživatele
        unset($_SESSION['user']);
        
        //Vymazat data z této instance uživatele
        $this->id = null;
        $this->hash = null;
        $this->lastChangelog = null;
        $this->lastLevel = null;
        $this->lastFolder = null;
        $this->theme = null;
        $this->name = null;
        $this->email = null;
        $this->lastLogin = null;
        $this->addedPictures = null;
        $this->guessedPictures = null;
        $this->karma = null;
        $this->status = null;
        
        return true;
    }
}