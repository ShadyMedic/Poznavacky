<?php
namespace Poznavacky\Models\DatabaseItems;

use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\undefined;
use \ArrayAccess;
use \BadMethodCallException;
use \DateTime;
use \Exception;

/**
 * Třída uchovávající data o uživateli (ne nutně přihlášeném)
 * @author Jan Štěch
 */
class User extends DatabaseItem implements ArrayAccess
{
    public const TABLE_NAME = 'uzivatele';
    
    public const COLUMN_DICTIONARY = array(
        'id' => 'uzivatele_id',
        'name' => 'jmeno',
        'email' => 'email',
        'lastLogin' => 'posledni_prihlaseni',
        'addedPictures' => 'pridane_obrazky',
        'guessedPictures' => 'uhodnute_obrazky',
        'karma' => 'karma',
        'status' => 'status'
    );
    
    protected const NON_PRIMITIVE_PROPERTIES = array(/* Žádná z vlastností neukládá objekt */
    );
    
    protected const DEFAULT_VALUES = array(
        'email' => null,
        'addedPictures' => 0,
        'guessedPictures' => 0,
        'karma' => 0,
    );
    
    protected const CAN_BE_CREATED = false;
    protected const CAN_BE_UPDATED = true;
    
    const STATUS_GUEST = 'Guest';
    const STATUS_MEMBER = 'Member';
    const STATUS_CLASS_OWNER = 'Class Owner';
    const STATUS_ADMIN = 'Administrator';
    
    public $name;
    protected $email;
    protected $lastLogin;
    protected $addedPictures;
    protected $guessedPictures;
    protected $karma;
    protected $status;
    
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
    public function initialize($name = null, $email = null, $lastLogin = null, $addedPictures = null,
                               $guessedPictures = null, $karma = null, $status = null): void
    {
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($name === null) {
            $name = $this->name;
        }
        if ($email === null) {
            $email = $this->email;
        }
        if ($lastLogin === null) {
            $lastLogin = $this->lastLogin;
        }
        if ($addedPictures === null) {
            $addedPictures = $this->addedPictures;
        }
        if ($guessedPictures === null) {
            $guessedPictures = $this->guessedPictures;
        }
        if ($karma === null) {
            $karma = $this->karma;
        }
        if ($status === null) {
            $status = $this->status;
        }
        
        $this->name = $name;
        $this->email = $email;
        $this->lastLogin = $lastLogin;
        $this->addedPictures = $addedPictures;
        $this->guessedPictures = $guessedPictures;
        $this->karma = $karma;
        $this->status = $status;
    }
    
    /**
     * Metoda načítající z databáze aktuální pozvánky pro tohoto uživatele a navracející je jako pole objektů
     * @return Invitation[] Pole aktivních pozvánek jako objekty
     * @throws DatabaseException
     * @throws Exception Pokud se nepodaří vytvořit objekt DateTime
     */
    public function getActiveInvitations(): array
    {
        $this->loadIfNotLoaded($this->id);
        
        $invitationsData = Db::fetchQuery('SELECT '.Invitation::COLUMN_DICTIONARY['id'].','.
                                          Invitation::COLUMN_DICTIONARY['class'].','.
                                          Invitation::COLUMN_DICTIONARY['expiration'].' FROM '.Invitation::TABLE_NAME.
                                          ' WHERE '.Invitation::COLUMN_DICTIONARY['user'].' = ? AND expirace > NOW()',
            array($this->id), true);
        if ($invitationsData === false) {
            //Žádné pozvánky
            return array();
        }
        
        $invitations = array();
        
        foreach ($invitationsData as $invitationData) {
            $invitation = new Invitation(false, $invitationData[Invitation::COLUMN_DICTIONARY['id']]);
            $invitation->initialize($this,
                new ClassObject(false, $invitationData[Invitation::COLUMN_DICTIONARY['class']]),
                new DateTime($invitationData[Invitation::COLUMN_DICTIONARY['expiration']]));
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
     * @return boolean TRUE, pokud jsou uživatelova data úspěšně aktualizována
     * @throws DatabaseException
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem nebo jsou zadaná data neplatná
     */
    public function updateAccount(int $addedPictures, int $guessedPictures, int $karma, string $status): bool
    {
        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        $aChecker = new AccessChecker();
        if (!$aChecker->checkSystemAdmin()) {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
        
        //Kontrola platnosti dat
        if ($addedPictures < 0 || $guessedPictures < 0 ||
            !($status === self::STATUS_ADMIN || $status === self::STATUS_CLASS_OWNER ||
              $status === self::STATUS_MEMBER || $status === self::STATUS_GUEST)) {
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
     * @return boolean TRUE, pokud je uživatel úspěšně odstraněn z databáze
     * @throws DatabaseException
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem nebo pokud odstraňovaný uživatel
     *     spravuje nějakou třídu
     */
    public function deleteAccountAsAdmin(): bool
    {
        $this->loadIfNotLoaded($this->id);
        
        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        $aChecker = new AccessChecker();
        if (!$aChecker->checkSystemAdmin()) {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
        
        //Kontrola, zda uživatel není správcem žádné třídy
        $administratedClasses = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM '.ClassObject::TABLE_NAME.' WHERE '.
                                               ClassObject::COLUMN_DICTIONARY['admin'].' = ? LIMIT 1',
            array($this->id));
        if ($administratedClasses['cnt'] > 0) {
            throw new AccessDeniedException(AccessDeniedException::REASON_ADMINISTRATION_ACCOUNT_DELETION_ADMINISTRATOR);
        }
        
        //Kontrola dat OK
        
        $this->delete();
        
        return true;
    }
    
    /**
     * Metoda pro zjišťování existence některé vlastnosti uživatele
     * {@inheritDoc}
     * @throws DatabaseException
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset): bool
    {
        $this->loadIfNotLoaded($this->$offset);
        return (isset($this->$offset));
    }
    
    /**
     * Metoda pro získání hodnoty nějaké z vlastností uživatele
     * {@inheritDoc}
     * @throws DatabaseException
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
     * @throws BadMethodCallException Při pokusu změnit ID
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value): void
    {
        if ($offset !== 'id') {
            $this->$offset = $value;
        } else {
            throw new BadMethodCallException('It isn\'t allowed to edit user\'s ID.');
        }
    }
    
    /**
     * Metoda pro odebrání hodnoty nějaké z vlastností uživatele
     *
     * Nelze použít pro odebrání jakékoliv vlastnosti
     * {@inheritDoc}
     * @throws BadMethodCallException Při pokusu odebrat jakoukoli vlastnost
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException('It isn\'t allowed to remove user\'s properities.');
    }
}

