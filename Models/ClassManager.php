<?php

/** 
 * Třída získávající informace o třídě z databáze, například za účelem pro získání ID z názvu a obráceně
 * Dále ověřuje zda třída nebo poznávačka do ní patřící existuje.
 * @author Jan Štěch
 */
class ClassManager
{
    /**
     * Metoda získávající ID třídy podle jejího názvu
     * @param string $name Název třídy
     * @return int ID třídy
     */
    public static function getIdByName(string $name)
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT tridy_id FROM tridy WHERE nazev = ?', array($name), false);
        return $result['tridy_id'];
    }
    
    /**
     * Metoda získávající název třídy podle jejího ID
     * @param int $id ID třídy
     * @return string Název třídy
     */
    public static function getNameById(int $id)
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT nazev FROM tridy WHERE tridy_id = ?', array($id), false);
        return $result['nazev'];
    }
    
    /**
     * Metoda kontrolující, zda třída daného jména existuje
     * @param string $className Jméno třídy
     * @return boolean TRUE, pokud byla třída nalezene, FALSE, pokud ne
     */
    public static function classExists(string $className)
    {
        Db::connect();
        $cnt = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM tridy WHERE nazev = ?', array($className), false);
        if ($cnt['cnt'] > 0)
        {
            return true;
        }
        return false;
    }
    
    /**
     * Metoda kontrolující, zda v dané třídě existuje specifikovaná poznávačka
     * @param string $className Jméno třídy
     * @param string $groupName Jméno poznávačky
     * @return boolean TRUE, pokud byla poznávačka nalezene, FALSE, pokud ne
     */
    public static function groupExists(string $className, string $groupName)
    {
        $handle = fopen('log.txt', 'a');
        fwrite($handle, 'Kontroluji existenci poznávačky '.$groupName.' ve třídě '.$className.'...');
        fclose($handle);
        Db::connect();
        $cnt = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM poznavacky WHERE nazev = ? AND tridy_id = ?', array($groupName, self::getIdByName($className)), false);
        if ($cnt['cnt'] > 0)
        {
           $handle = fopen('log.txt', 'a');
           fwrite($handle, 'Úspěch : '.$cnt['cnt']);
           fclose($handle);
           return true;
        }
        $handle = fopen('log.txt', 'a');
        fwrite($handle, 'Selhání : '.$cnt['cnt']);
        fclose($handle);
        return false;
    }
}