<?php
namespace Poznavacky\Models\Processors;

use Poznavacky\Models\Emails\EmailComposer;
use Poznavacky\Models\Emails\EmailSender;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Security\DataValidator;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\Logger;
use Exception;

/** 
 * Třída generující kód pro obnovu hesla a odesílající jej na uživatelův e-mail
 * @author Jan Štěch
 */
class RecoverPassword
{
    //Počet náhodných bajtů, ze kterých se tvoří kód
    private const CODE_BYTE_LENGTH = 16;   //16 bytů --> 128 bitů --> maximálně třicetidvoumístný kód
    private const EMAIL_SUBJECT = 'Žádost o obnovu hesla na Poznávačkách';
    private const CODE_EXPIRATION = 86400;   //86400 sekund --> 24 hodin

    /**
     * Metoda starající se o celý proces obnový hesla
     * @param array $POSTdata Data obdržená z formuláře na index stránce
     * @return boolean TRUE, Pokud se všechny kroky podařily, FALSE, pokud se nepodařilo odeslat e-mail
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     * @throws AccessDeniedException Pokud nebyl vyplněn platný e-mail
     */
    public function processRecovery(array $POSTdata): bool
    {
        if (mb_strlen($POSTdata['email']) === 0)
        {
            (new Logger(true))->notice('Pokus o odeslání e-mailu pro obnovu hesla z IP adresy {ip} selhal kvůli nevyplnění e-mailové adresy', array('ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_RECOVERY_NO_EMAIL, null, null);
        }
        $email = $POSTdata['email'];
        
        $validator = new DataValidator();
        try { $userId = $validator->getUserIdByEmail($email); }
        catch (AccessDeniedException $e)
        {
            (new Logger(true))->notice('Pokus o odeslání e-mailu pro obnovu hesla z IP adresy {ip} na adresu {email} selhal, protože tato adresa nepatří žádnému zaregistrovanému uživateli', array('ip' => $_SERVER['REMOTE_ADDR'], 'email' => $email));
            throw $e;   //Výjimka je zachycena v kontroleru
        }
        
        $code = self::generateCode();
        self::saveCode($code, $userId);
        $result = self::sendCode($code, $email);

        if ($result) { (new Logger(true))->info('E-mail s kódem pro obnovu hesla byl odeslán na e-mailovou adresu {email} na základě požadavku z IP adresy {ip}', array('email' => $email, 'ip' => $_SERVER['REMOTE_ADDR'])); }
        else { (new Logger(true))->critical('E-mail s kódem pro obnovu hesla se na e-mailovou adresu {email} na základě požadavku z IP adresy {ip} nepodařilo z neznámého důvodu odeslat; je možné že není možné odesílat žádné e-maily', array('email' => $email, 'ip' => $_SERVER['REMOTE_ADDR'])); }
        return $result;
    }

    /**
     * Metoda generující náhodný kód pro obnovu hesla.
     * Metoda zajišťuje, že je kód unikátní a ještě se v databázi nevyskytuje.
     * @return string Vygenerovaný unikátní kód
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     * @throws Exception Pokud se nepodaří vygenerovat kód pro obnovu hesla
     */
    private function generateCode(): string
    {
        $done = false;
        $code = NULL;
        do
        {
            //Vygenerovat třicetidvoumístný kód pro obnovení hesla
            $code = bin2hex(random_bytes(self::CODE_BYTE_LENGTH));
            
            //Zkontrolovat, zda již hash kódů v databázi neexistuje
            $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM obnoveni_hesel WHERE kod=?', array(md5($code)), FALSE);
            //Kontrola případné potřeby opakování generování kódu
            if ($result['cnt'] === 0)
            {
                $done = true;
            }
        } while ($done == false);
        return $code;
    }

    /**
     * Metoda ukládající hash kódu pro obnovu hesla do databáze společně s ID uživatele, který jej může použít
     * @param string $code Nezašifrovaný kód pro obnovu hesla
     * @param int $userId ID uživatele, jež si pomocí kódu může změnit heslo
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     */
    private function saveCode(string $code, int $userId): void
    {
        //Smazat starý kód z databáze (pokud existuje)
        Db::executeQuery('DELETE FROM obnoveni_hesel WHERE uzivatele_id = ?', array($userId));
        
        //Uložit kód do databáze
        Db::executeQuery('INSERT INTO obnoveni_hesel (kod, uzivatele_id, expirace) VALUES (?,?,?)', array(md5($code), $userId, time() + self::CODE_EXPIRATION));
    }
    
    /**
     * Metoda odesílající uživateli e-mail s odkazem obsahujícím kód k obnovení hesla
     * @param string $code Kód pro obnovení hesla k odeslání
     * @param string $email E-mailová adresa pro odeslání e-mailu
     * @return bool TRUE, pokud se e-mail podařilo odeslat, FALSE, pokud ne
     */
    private function sendCode(string $code, string $email): bool
    {
        $message = new EmailComposer();
        $message->composeMail(EmailComposer::EMAIL_TYPE_PASSWORD_RECOVERY, array('recoveryLink' => $_SERVER['SERVER_NAME'].'/recover-password/'.$code));
        
        $sender = new EmailSender();
        return $sender->sendMail($email, self::EMAIL_SUBJECT, $message->getMail());
    }
}

