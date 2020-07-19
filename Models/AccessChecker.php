<?php

/** 
 * Třída kontrolující, zda má nějaký uživatel přístup do nějaké třídy nebo její součásti
 * @author Jan Štěch
 */
class AccessChecker
{
    /**
     * Metoda kontrolující, zda je přihlášený nějaký uživatel
     * @return boolean TRUE, pokud je nějaký uživatel přihlášen, FALSE, pokud ne
     */
    public static function checkUser()
    {
        return (isset($_SESSION['user']));
    }
    
    /**
     * Metoda ověřující, zda je řetězec heslem aktuálně přihlášeného uživatele
     * @param string $password Heslo k ověření
     * @return boolean TRUE, pokud je specifikované heslo správné, FALSE, pokud ne
     */
    public static function recheckPassword(string $password)
    {
        if (password_verify($password, UserManager::getHash()))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * 
     * Metoda kontrolující, zda je přihlášený uživatel systémovým správcem
     * @return boolean TRUE, pokud je daný uživatelem systémovým správcem, FALSE, pokud ne
     */
    public static function checkSystemAdmin()
    {
        return (UserManager::getOtherInformation()['status'] === User::STATUS_ADMIN) ? true : false;
    }
}

