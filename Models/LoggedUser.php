<?php
/** 
 * Třída uchovávající data o právě přihlášeném uživateli
 * @author Jan Štěch
 */
class LoggedUser extends User
{
    static $isLogged = false;
    protected $hash;
    protected $lastChangelog;
    protected $lastLevel;
    protected $lastFolder;
    protected $theme;
    
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
}