<?php
/** 
 * Třída generující kód pro obnovu hesla a odesílající jej na uživatelův e-mail
 * @author Jan Štěch
 */
class RecoverPassword
{
    //Počet náhodných bajtů, ze kterých se tvoří kód
    private const CODE_BYTE_LENGTH = 16;   //16 bytů --> 128 bitů --> maximálně třicetidvoumístný kód
    private const EMAIL_SUBJECT = 'Žádost o obnovu hesla na Poznávačkách';
    
    /**
     * Metoda starající se o celý proces obnový hesla
     * @param array $POSTdata Data obdržená z formuláře na index stránce
     * @throws AccessDeniedException Pokud nebyl vyplněn platný e-mail
     * @return boolean Pokud se všechny kroky podařily
     */
    public static function processRecovery(array $POSTdata)
    {
        if (!isset($POSTdata['email']))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_RECOVERY_NO_EMAIL, null, null, array('originFile' => 'RecoverPassword.php', 'displayOnView' => 'index.phtml', 'form' => 'passRecovery'));
        }
        $email = $POSTdata['email'];
        
        $validator = new DataValidator();
        $userId = $validator->getUserIdByEmail($email);
        
        $code = self::generateCode();
        self::saveCode($code, $userId);
        self::sendCode($code, $email);
        
        return true;
    }
    
    /**
     * Metoda generující náhodný kód pro obnovu hesla.
     * Metoda zajišťuje, že je kód unikátní a ještě se v databázi nevyskytuje.
     * @return string Vygenerovaný unikátní kód
     */
    private static function generateCode()
    {
        $done = false;
        $code = NULL;
        do
        {
            //Vygenerovat třicetidvoumístný kód pro obnovení hesla
            $code = bin2hex(random_bytes(self::CODE_BYTE_LENGTH));
            
            //Zkontrolovat, zda již kód v databázi neexistuje
            Db::connect();
            $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM obnoveni_hesel WHERE kod=?', array(md5($code)), FALSE);
            //Kontrola případné potřeby opakování generování kódu
            if ($result['cnt'] === 0)
            {
                $done = true;
            }
        }while ($done == false);
        return $code;
    }
    
    /**
     * Metoda ukládající hash kódu pro obnovu hesla do databáze společně s ID uživatele, který jej může použít
     * @param string $code Nezašifrovaný kód pro obnovu hesla
     * @param int $userId ID uživatele, jež si pomocí kódu může změnit heslo
     */
    private static function saveCode(string $code, int $userId)
    {
        //Smazat starý kód z databáze (pokud existuje)
        Db::connect();
        Db::executeQuery('DELETE FROM obnoveni_hesel WHERE uzivatele_id=?', array($userId));
        
        //Uložit kód do databáze
        Db::executeQuery('INSERT INTO obnoveni_hesel (kod, uzivatele_id) VALUES (?,?)', array(md5($code), $userId));
    }
    
    /**
     * Metoda odesílající uživateli e-mail s odkazem obsahujícím kód k obnovení hesla
     * @param string $code
     * @param string $email
     */
    private static function sendCode(string $code, string $email)
    {
        $message = new EmailComposer();
        $message->composeMail(EmailComposer::EMAIL_TYPE_PASSWORD_RECOVERY, array('recoveryLink' => $_SERVER['SERVER_NAME'].'/recover-password/'.$code));
        
        $sender = new EmailSender();
        $sender->sendMail($email, self::EMAIL_SUBJECT, $message->getMail());
    }
}

