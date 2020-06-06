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
     * Metoda kontrolující, zda je určitý uživatel systémovým správcem
     * @param int $userId ID ověřovaného uživatele
     * @param bool $loadFromDb TRUE, pokud se má status načíst z databáze, což je o něco bezpečnější, ale náročnější. Pro běžné úkony stačí ponechat defaultní FALSE což porovná hodnotu uloženou v $_SESSION['user']['status']
     * @return boolean TRUE, pokud je daný uživatelem systémovým správcem, FALSE, pokud ne
     */
    public static function checkSystemAdmin(int $userId, bool $loadFromDb = false)
    {
        if ($loadFromDb)
        {
            Db::connect();
            $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM uzivatele WHERE uzivatele_id = ? AND status = ?', array($userId, User::STATUS_ADMIN), false);
            return ($result['cnt'] === 1) ? true : false;
        }
        else
        {
            return (UserManager::getOtherInformation()['status'] === User::STATUS_ADMIN) ? true : false;
        }
    }
}

