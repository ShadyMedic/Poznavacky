<?php

/** 
 * Třída kontrolující, zda má nějaký uživatel přístup do nějaké třídy nebo její součásti
 * @author Jan Štěch
 */
class AccessChecker
{
    /**
     * Metoda kontrolující, zda má určitý uživatel přístup do určité třídy
     * @param int $userId ID ověřovaného uživatele
     * @param int $classId ID ověřované třídy
     * @return boolean TRUE, pokud má uživatel přístup do třídy, FALSE pokud ne
     */
    public static function checkAccess(int $userId, int $classId)
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM `tridy` WHERE tridy_id = ? AND (status = "public" OR tridy_id IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = ?));', array($classId, $userId), false);
        return ($result['cnt'] === 1) ? true : false;
    }
    
    /**
     * Metoda kontrolující, zda je určitý uživatel správcem určité třídy
     * @param int $userId ID ověřovaného uživatele
     * @param int $classId ID ověřované třídy
     * @return boolean TRUE, pokud je uživatelem správce třídy, FALSE pokud ne
     */
    public static function checkAdmin(int $userId, int $classId)
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM `tridy` WHERE tridy_id = ? AND spravce = ?;', array($classId, $userId), false);
        return ($result['cnt'] === 1) ? true : false;
    }
    
    /**
     * Metoda kontrolující, zda je určitý uživatel systémovým správcem
     * @param int $userId ID ověřovaného uživatele
     */
    public static function checkSystemAdmin(int $userId)
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM uzivatele WHERE uzivatele_id = ? AND status = "admin"', array($userId), false);
        return ($result['cnt'] === 1) ? true : false;
    }
}

