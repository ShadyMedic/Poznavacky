<?php
/** 
 * Třída uchovávající data o uživateli (ne nutně přihlášeném)
 * @author Jan Štěch
 */
class User implements ArrayAccess
{
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
     * Konstruktor pro nového uživatele
     * @param int $id ID uživatele v databázi
     * @param string $name Přezdívka uživatele
     * @param string $email E-mailová adresa uživatele
     * @param DateTime $lastLogin Datum a čas posledního přihlášení uživatele
     * @param int $addedPictures Počet obrázků přidaných uživatelem
     * @param int $guessedPictures Počet obrázků uhodnutých uživatelem
     * @param int $karma Uživatelova karma
     * @param string $status Uživatelův status
     */
    public function __construct(int $id, string $name, string $email = null, DateTime $lastLogin = null, int $addedPictures = 0, int $guessedPictures = 0, int $karma = 0, string $status = self::STATUS_MEMBER)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->lastLogin = $lastLogin;
        $this->addedPictures = $addedPictures;
        $this->guessedPictures = $guessedPictures;
        $this->karma = $karma;
        $this->status = $status;
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
        $applications = Db::fetchQuery('SELECT zadosti_jmena_id FROM zadosti_jmena WHERE uzivatele_jmeno = ?', array(UserManager::getName()));
        if (!empty($applications['zadosti_jmena_id']))
        {
            //Přepsání existující žádosti
            Db::executeQuery('UPDATE zadosti_jmena SET nove = ?, cas = ? WHERE zadosti_jmena_id = ? LIMIT 1', array($newName, time(), $applications['zadosti_jmena_id']));
        }
        else
        {
            //Uložení nové žádosti
            Db::executeQuery('INSERT INTO zadosti_jmena (uzivatele_jmeno,nove,cas) VALUES (?,?,?)', array($this->name, $newName, time()));
        }
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
        if ($newEmail !== NULL)
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
        
        //Aktualizovat databáz
        Db::connect();
        Db::executeQuery('UPDATE uzivatele SET email = ? WHERE uzivatele_id = ? LIMIT 1', array($newEmail, UserManager::getId()));
        $this->email = $newEmail;
        return true;
    }
    
    /**
     * Metoda ověřující heslo přihlášeného uživatele a v případě úspěchu odstraňující jeho uživatelský účet
     * Po odstranění z databáze jsou uživatelova data vymazána i ze $_SESSION
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
        return true;
    }
    
    /**
     * Metoda pro zjišťování existence některé vlastnosti uživatele
     * {@inheritDoc}
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return (isset($this->$offset));
    }
    
    /**
     * Metoda pro získání hodnoty nějaké z vlastností uživatele
     * {@inheritDoc}
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
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