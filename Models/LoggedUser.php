<?php
/** 
 * Třída uchovávající data o právě přihlášeném uživateli
 * @author Jan Štěch
 */
class LoggedUser extends User
{
    static $isLogged = false;
    private $hash;
    private $lastChangelog;
    private $lastLevel;
    private $lastFolder;
    private $theme;
    
    /**
     *
     * @param int $id ID uživatele v databázi
     * @param string $name Přezdívka uživatele
     * @param string $hash Heš uživatelova hesla z databáze
     * @param string $email E-mailová adresa uživatele
     * @param DateTime $lastLogin Datum a čas posledního přihlášení uživatele
     * @param float $lastChangelog Poslední zobrazený changelog
     * @param int $lastLevel Poslední navštívěná úroveň složek na menu stránce
     * @param int $lastFolder Poslední navštívená složka na menu stránce v určité úrovni
     * @param int $theme Zvolený vzhled stránek
     * @param int $addedPictures Počet obrázků přidaných uživatelem
     * @param int $guessedPictures Počet obrázků uhodnutých uživatelem
     * @param int $karma Uživatelova karma
     * @param string $status Uživatelův status
     */
    public function __construct(int $id, string $name, string $hash, string $email = null, DateTime $lastLogin = null, float $lastChangelog = 0, int $lastLevel = 0, int $lastFolder = null, int $theme = 0, int $addedPictures = 0, int $guessedPictures = 0, int $karma = 0, string $status = self::STATUS_MEMBER)
    {
        parent::__construct($id, $name, $email, $lastLogin, $addedPictures, $guessedPictures, $karma, $status);
        $this->hash = $hash;
        $this->lastChangelog = $lastChangelog;
        $this->lastLevel = $lastLevel;
        $this->lastFolder = $lastFolder;
        $this->theme = $theme;
    }
}