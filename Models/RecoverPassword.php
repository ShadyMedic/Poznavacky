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
    
    public static function processRecovery(array $POSTdata)
    {
        if (!isset($POSTdata['email_input']))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_RECOVERY_NO_EMAIL, null, null, array('originFile' => 'RecoverPassword.php', 'displayOnView' => 'index.phtml', 'form' => 'passRecovery'));
        }
        $email = $POSTdata['email_input'];
        
        $userId = self::getUserIdByEmail($email);
        
        $code = self::generateCode();
        self::saveCode($code, $userId);
        self::sendCode($code, $email);
        
        return true;
    }
    
    private static function getUserIdByEmail(string $email)
    {
        Db::connect();
        $userId = Db::fetchQuery('SELECT uzivatele_id FROM uzivatele WHERE email = ? LIMIT 1', array($email), false);
        if (!$userId)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_RECOVERY_NO_ACCOUNT, null, null, array('originFile' => 'RecoverPassword.php', 'displayOnView' => 'index.phtml', 'form' => 'passRecovery'));
        }
        return $userId['uzivatele_id'];
    }
    
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
    
    private static function saveCode($code, $userId)
    {
        //Smazat starý kód z databáze (pokud existuje)
        Db::connect();
        Db::executeQuery('DELETE FROM obnoveni_hesel WHERE uzivatele_id=?', array($userId));
        
        //Uložit kód do databáze
        Db::executeQuery('INSERT INTO obnoveni_hesel (kod, uzivatele_id) VALUES (?,?)', array(md5($code), $userId));
    }
    
    private static function sendCode($code, $email)
    {
        $message = new EmailComposer();
        $message->composeMail(EmailComposer::EMAIL_TYPE_PASSWORD_RECOVERY, array('recoveryLink' => $_SERVER['SERVER_NAME'].'/recover-password/'.$code));
        
        $sender = new EmailSender();
        $sender->sendMail($email, self::EMAIL_SUBJECT, $message->getMail());
    }
}

