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
     * Metoda kontrolující, zda je určitý uživatel systémovým správcem
     * @param int $userId ID ověřovaného uživatele
     * @return boolean TRUE, pokud je daný uživatelem systémovým správcem, FALSE, pokud ne
     */
    public static function checkSystemAdmin(int $userId)
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM uzivatele WHERE uzivatele_id = ? AND status = "admin"', array($userId), false);
        return ($result['cnt'] === 1) ? true : false;
    }
}

