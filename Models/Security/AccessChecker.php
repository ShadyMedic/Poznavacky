<?php
namespace Poznavacky\Models\Security;

use Poznavacky\Models\DatabaseItems\User;
use Poznavacky\Models\Statics\UserManager;

/** 
 * Třída kontrolující, status přihlášeného uživatele, ověřující jeho heslo nebo kontrolující, zda je zvolena třída, poznávačka nebo část
 * @author Jan Štěch
 */
class AccessChecker
{
    /**
     * Metoda kontrolující, zda je přihlášený nějaký uživatel
     * @return boolean TRUE, pokud je nějaký uživatel přihlášen, FALSE, pokud ne
     */
    public function checkUser(): bool
    {
        return (isset($_SESSION['user']));
    }
    
    /**
     * Metoda ověřující, zda je řetězec heslem aktuálně přihlášeného uživatele
     * @param string $password Heslo k ověření
     * @return boolean TRUE, pokud je specifikované heslo správné, FALSE, pokud ne
     */
    public function recheckPassword(string $password): bool
    {
        if (password_verify($password, UserManager::getHash())) { return true; }
        else { return false; }
    }
    
    /**
     * Metoda kontrolující, zda je přihlášený uživatel systémovým správcem
     * @return boolean TRUE, pokud je daný uživatelem systémovým správcem, FALSE, pokud ne
     */
    public function checkSystemAdmin(): bool
    {
        return UserManager::getOtherInformation()['status'] === User::STATUS_ADMIN;
    }

    /**
     * Metoda kontrolující, zda používá přihlášený uživatel demo účet
     * @return bool TRUE, pokud se jedná o demo účet, FALSE, pokud ne
     */
    public function checkDemoAccount(): bool
    {
        return UserManager::getOtherInformation()['status'] === User::STATUS_GUEST;
    }

    /**
     * Metoda kontrolující, zda je zvolena nějaká třída
     * @return bool TRUE, pokud je nějaká platná třída zvolena, FALSE, pokud ne
     */
    public function checkClass(): bool
    {
        return (isset($_SESSION['selection']['class']));
    }

    /**
     * Metoda kontrolující, zda je zvolena nějaká poznávačka
     * @return bool TRUE, pokud je nějaká platná poznávačka zvolena, FALSE, pokud ne
     */
    public function checkGroup(): bool
    {
        return (isset($_SESSION['selection']['group']));
    }

    /**
     * Metoda kontrolující, zda je zvolena nějaká část
     * @return bool TRUE, pokud je nějaká platná část zvolena, FALSE, pokud ne
     */
    public function checkPart(): bool
    {
        return (isset($_SESSION['selection']['part']));
    }
}

