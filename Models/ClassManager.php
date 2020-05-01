<?php

/** 
 * Třída získávající informace o třídě z databáze, obvykle pro získání ID z názvu a obráceně
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
}