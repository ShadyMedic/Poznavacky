<?php
namespace Poznavacky\Models\DatabaseItems;

use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Security\DataValidator;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\undefined;
use \DateTime;
use \InvalidArgumentException;
use \RangeException;

/** 
 * Třída uchovávající data o právě přihlášeném uživateli
 * @author Jan Štěch
 */
class LoggedUser extends User
{
    public const COLUMN_DICTIONARY = array(
        'id' => 'uzivatele_id',
        'name' => 'jmeno',
        'hash' => 'heslo',
        'email' => 'email',
        'lastLogin' => 'posledni_prihlaseni',
        'lastChangelog' => 'posledni_changelog',
        'lastLevel' => 'posledni_uroven',
        'lastFolder' => 'posledni_slozka',
        'addedPictures' => 'pridane_obrazky',
        'guessedPictures' => 'uhodnute_obrazky',
        'karma' => 'karma',
        'status' => 'status'
    );
    
    protected const NON_PRIMITIVE_PROPERTIES = array(
        /* Žádná z vlastností neukládá objekt */
    );
    
    protected const DEFAULT_VALUES = array(
        'email' => null,
        'lastChangelog' => 0,
        'lastLevel' => 0,
        'lastFolder' => null,
        'addedPictures' => 0,
        'guessedPictures' => 0,
        'karma' => 0,
        'status' => self::STATUS_MEMBER,
    );
    
    protected const CAN_BE_CREATED = true;
    protected const CAN_BE_UPDATED = true;
    
    protected $hash;
    protected $lastChangelog;
    protected $lastLevel;
    protected $lastFolder;
    
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
     * @param string|undefined|null $hash Heš uživatelova hesla z databáze
     * @param float|undefined|null $lastChangelog Poslední zobrazený changelog
     * @param int|undefined|null $lastLevel Poslední navštívěná úroveň složek na menu stránce
     * @param int|undefined|null $lastFolder Poslední navštívená složka na menu stránce v určité úrovni
     * {@inheritDoc}
     * @see User::initialize()
     */
    public function initialize($name = null, $email = null, $lastLogin = null, $addedPictures = null, $guessedPictures = null, $karma = null, $status = null, $hash = null, $lastChangelog = null, $lastLevel = null, $lastFolder = null): void
    {
        //Nastav vlastnosti zděděné z mateřské třídy
        parent::initialize($name, $email, $lastLogin, $addedPictures, $guessedPictures, $karma, $status);
        
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($hash === null){ $hash = $this->hash; }
        if ($lastChangelog === null){ $lastChangelog = $this->lastChangelog; }
        if ($lastLevel === null){ $lastLevel = $this->lastLevel; }
        if ($lastFolder === null){ $lastFolder = $this->lastFolder; }
        
        $this->hash = $hash;
        $this->lastChangelog = $lastChangelog;
        $this->lastLevel = $lastLevel;
        $this->lastFolder = $lastFolder;
    }
    
    /**
     * Metoda ukládající do databáze nový požadavek na změnu jména od přihlášeného uživatele, pokud žádný takový požadavek neexistuje nebo aktualizující stávající požadavek
     * Data jsou předem ověřena
     * @param string $newName Požadované nové jméno
     * @throws AccessDeniedException Pokud jméno nevyhovuje podmínkám systému
     * @return boolean TRUE, pokud je žádost úspěšně vytvořena/aktualizována
     */
    public function requestNameChange(string $newName): bool
    {
        if (mb_strlen($newName) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_NAME_CHANGE_NO_NAME);}
        
        //Kontrola délky jména
        $validator = new DataValidator();
        try
        {
            $validator->checkLength($newName, DataValidator::USER_NAME_MIN_LENGTH, DataValidator::USER_NAME_MAX_LENGTH, DataValidator::TYPE_USER_NAME);
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
            $validator->checkCharacters($newName, DataValidator::USER_NAME_ALLOWED_CHARS, DataValidator::TYPE_USER_NAME);
        }
        catch (InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_NAME_CHANGE_INVALID_CHARACTERS, null, $e);
        }
        
        //Kontrola dostupnosti jména
        try
        {
            $validator->checkUniqueness($newName, DataValidator::TYPE_USER_NAME);
        }
        catch (InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_NAME_CHANGE_DUPLICATE_NAME, null, $e);
        }
        
        //Kontrola dat OK
        
        //Zkontrolovat, zda již existuje žádost o změnu jména od přihlášeného uživatele
        $applications = Db::fetchQuery('SELECT '.UserNameChangeRequest::COLUMN_DICTIONARY['id'].' FROM '.UserNameChangeRequest::TABLE_NAME.' WHERE '.UserNameChangeRequest::COLUMN_DICTIONARY['subject'].' = ? LIMIT 1', array(UserManager::getId()));
        if (!empty($applications[UserNameChangeRequest::COLUMN_DICTIONARY['id']]))
        {
            //Přepsání existující žádosti
            $this->loadIfNotLoaded($this->id);
            
            Db::executeQuery('UPDATE '.UserNameChangeRequest::TABLE_NAME.' SET '.UserNameChangeRequest::COLUMN_DICTIONARY['newName'].' = ?, '.UserNameChangeRequest::COLUMN_DICTIONARY['requestedAt'].' = NOW() WHERE '.UserNameChangeRequest::COLUMN_DICTIONARY['id'].' = ? LIMIT 1', array($newName, $applications[UserNameChangeRequest::COLUMN_DICTIONARY['id']]));
        }
        else
        {
            //Uložení nové žádosti
            Db::executeQuery('INSERT INTO '.UserNameChangeRequest::TABLE_NAME.' ('.UserNameChangeRequest::COLUMN_DICTIONARY['subject'].','.UserNameChangeRequest::COLUMN_DICTIONARY['newName'].','.UserNameChangeRequest::COLUMN_DICTIONARY['requestedAt'].') VALUES (?,?,NOW())', array($this->id, $newName));
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
    public function changePassword(string $oldPassword, string $newPassword, string $newPasswordAgain): bool
    {
        if (mb_strlen($oldPassword) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_NO_OLD_PASSWORD);}
        if (mb_strlen($newPassword) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_NO_PASSWORD);}
        if (mb_strlen($newPasswordAgain) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_NO_REPEATED_PASSWORD);}
        
        //Kontrola hesla
        $aChecker = new AccessChecker();
        if (!$aChecker->recheckPassword($oldPassword))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_WRONG_PASSWORD);
        }
        
        //Kontrola délky nového hesla
        $validator = new DataValidator();
        try
        {
            $validator->checkLength($newPassword, DataValidator::USER_PASSWORD_MIN_LENGTH, DataValidator::USER_PASSWORD_MAX_LENGTH, DataValidator::TYPE_USER_PASSWORD);
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
            $validator->checkCharacters($newPassword, DataValidator::USER_PASSWORD_ALLOWED_CHARS, DataValidator::TYPE_USER_PASSWORD);
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
        $this->loadIfNotLoaded($this->id);
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET '.self::COLUMN_DICTIONARY['hash'].' = ? WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1', array($hashedPassword, UserManager::getId()));
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
    public function changeEmail(string $password, string $newEmail): bool
    {
        if (mb_strlen($password) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_EMAIL_CHANGE_NO_PASSWORD);}
        if (mb_strlen($newEmail) === 0){$newEmail = NULL;}
        
        //Kontrola hesla
        $aChecker = new AccessChecker();
        if (!$aChecker->recheckPassword($password))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_EMAIL_CHANGE_WRONG_PASSWORD);
        }
        
        //Kontrola délky a unikátnosti e-mailu (pokud ho uživatel nechce odstranit)
        if (!empty($newEmail))
        {
            $validator = new DataValidator();
            try
            {
                $validator->checkLength($newEmail, DataValidator::USER_EMAIL_MIN_LENGTH, DataValidator::USER_EMAIL_MAX_LENGTH, DataValidator::TYPE_USER_EMAIL);
                $validator->checkUniqueness($newEmail, DataValidator::TYPE_USER_EMAIL);
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
        $this->loadIfNotLoaded($this->id);
        
        Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET '.self::COLUMN_DICTIONARY['email'].' = ? WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1', array($newEmail, UserManager::getId()));
        $this->email = $newEmail;
        return true;
    }
    
    /**
     * Metoda přidávající uživateli jak v $_SESSION tak v databázi jeden bod v poli přidaných obrázků
     * @return boolean TRUE, pokud vše proběhne hladce
     */
    public function incrementAddedPictures(): bool
    {
        $this->addedPictures++;
        return Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET '.self::COLUMN_DICTIONARY['addedPictures'].' = (pridane_obrazky + 1) WHERE '.self::COLUMN_DICTIONARY['id'].' = ?', array($this->id));
    }
    
    /**
     * Metoda přidávající uživateli jak v $_SESSION tak v databázi jeden bod v poli uhodnutých obrázků
     * @return boolean TRUE, pokud vše proběhne hladce
     */
    public function incrementGuessedPictures(): bool
    {
        $this->loadIfNotLoaded($this->id);
        
        $this->guessedPictures++;
        return Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET '.self::COLUMN_DICTIONARY['guessedPictures'].' = (uhodnute_obrazky + 1) WHERE '.self::COLUMN_DICTIONARY['id'].' = ?', array($this->id));
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
    public function deleteAccount(string $password): bool
    {
        if (mb_strlen($password) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_ACCOUNT_DELETION_NO_PASSWORD);}
        
        //Kontrola hesla
        $aChecker = new AccessChecker();
        if (!$aChecker->recheckPassword($password))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ACCOUNT_DELETION_WRONG_PASSWORD);
        }
        
        //Kontrola, zda uživatel není správcem žádné třídy
        $administratedClasses = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM '.ClassObject::TABLE_NAME.' WHERE '.ClassObject::COLUMN_DICTIONARY['admin'].' = ? LIMIT 1', array(UserManager::getId()));
        if ($administratedClasses['cnt'] > 0)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ACCOUNT_DELETION_CLASS_ADMINISTRATOR);
        }
        
        //Kontrola dat OK
        
        //Odstranit uživatele z databáze
        $result = $this->delete();
        
        //Odhlásit uživatele
        unset($_SESSION['user']);
        
        return $result;
    }
}

