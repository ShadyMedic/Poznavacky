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
    
    private $id;
    public $name;
    private $email;
    private $lastLogin;
    private $addedPictures;
    private $guessedPictures;
    private $karma;
    private $status;
    
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