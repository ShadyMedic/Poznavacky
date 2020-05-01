<?php
/** 
 * Třída ověřující, zda je kód pro obnovu hesla platný a jakému patří uživateli
 * @author Jan Štěch
 */
class PasswordRecoveryCodeVerificator
{
    /**
     * Metoda získávající ID uživatele s jehož účtem je svázán kód pro obnovu hesla uložený v databázi
     * @param string $code Kód pro obnovu hesla z URL adresy
     * @return int|boolean ID uživatele, který může použít tento kód pro obnovu svého hesla nebo FALSE, pokud kód nebyl v databázi nalezen
     */
    public static function verifyCode(string $code)
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT uzivatele_id FROM obnoveni_hesel WHERE kod = ?', array(md5($code)), false);
        if (!$result)
        {
            return false;
        }
        return $result['uzivatele_id'];
    }
    
    /**
     * Metoda odstraňující kódy pro obnovu hesla vytvořené déle než před jedním dnem z databáze
     */
    public static function deleteOutdatedCodes()
    {
        Db::connect();
        
        $yesterday = new DateTime('1 day ago');
        Db::executeQuery('DELETE FROM obnoveni_hesel WHERE vytvoreno < ?', array($yesterday->format('Y-m-d H:i:s')));
    }
    
    /**
     * Metoda odstraňující specifikovaný kód pro obnovu hesla z databáze
     * @param string $code Nezašifrovaný kód k odstranění
     */
    public static function deleteCode(string $code)
    {
        Db::connect();
        Db::executeQuery('DELETE FROM obnoveni_hesel WHERE kod = ?', array(md5($code)));
    }
}

