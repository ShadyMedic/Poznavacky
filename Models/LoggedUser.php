<?php
/** 
 * Třída uchovávající data o právě přihlášeném uživateli
 * @author Jan Štěch
 */
class LoggedUser extends User
{
    static $isLogged = false;
    private $hash;
    
    /**
     *
     * @param int $id ID uživatele v databázi
     * @param string $name Přezdívka uživatele
     * @param string $hash Heš uživatelova hesla z databáze
     * @param string $email E-mailová adresa uživatele
     * @param DateTime $lastLogin Datum a čas posledního přihlášení uživatele
     * @param int $addedPictures Počet obrázků přidaných uživatelem
     * @param int $guessedPictures Počet obrázků uhodnutých uživatelem
     * @param int $karma Uživatelova karma
     * @param string $status Uživatelův status
     */
    public function __construct(int $id, string $name, string $hash, string $email = null, DateTime $lastLogin = null, int $addedPictures = 0, int $guessedPictures = 0, int $karma = 0, string $status = self::STATUS_MEMBER)
    {
        parent::__construct($id, $name, $email, $lastLogin, $addedPictures, $guessedPictures, $karma, $status);
        $this->hash = $hash;
    }
}