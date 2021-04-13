<?php
namespace Poznavacky\Models\Processors;

use Poznavacky\Models\DatabaseItems\LoggedUser;
use Poznavacky\Models\DatabaseItems\User;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\Logger;
use \DateTime;
use \Exception;

/**
 * Třída ověřující uživatelovi přihlašovací údaje a přihlašující jej
 * @author Jan Štěch
 */
class LoginUser
{
    //Čas po jaký není nutné znovu přidávat heslo, pokud je při přihlášení zaškrtnuto políčko "Zůstat přihlášen"
    private const INSTALOGIN_COOKIE_LIFESPAN = 2592000;    //2 592 000 s = 30 dní

    /**
     * Metoda která se stará o všechny kroky přihlašování
     * @param array $POSTdata Data odeslaná přihlašovacím formulářem, pole s klíči name, pass a popřípadě stayLogged
     * @throws AccessDeniedException Pokud není nějaká z informací vyplněná nebo nesouhlasí jméno s heslem
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     */
    public function processLogin(array $POSTdata): void
    {
        $POSTdata['name'] = trim($POSTdata['name']); //Ořež mezery

        $errors = array();

        //Ověřit vyplněnost dat
        if (mb_strlen($POSTdata['name']) === 0) { $errors[] = AccessDeniedException::REASON_LOGIN_NO_NAME; }
        if (mb_strlen($POSTdata['pass']) === 0) { $errors[] = AccessDeniedException::REASON_LOGIN_NO_PASSWORD; }

        //Pokud není něco vyplněné, nemá smysl pokračovat
        if (!empty($errors))
        {
            (new Logger(true))->notice('Pokus o přihlášení z IP adresy {ip} selhal kvůli nevyplnění některého z údajů', array('ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(implode('|', $errors));
        }

        //Pokusit se přihlásit
        $userData = null;
        try
        {
            $userData = self::authenticate($POSTdata['name'], $POSTdata['pass']);
        }
        catch (AccessDeniedException $e)
        {
            (new Logger(true))->notice('Pokus o přihlášení z IP adresy {ip} k uživatelskému účtu {userName} selhal kvůli neshodě mezi zadanými údaji', array('ip' => $_SERVER['REMOTE_ADDR'], 'userName' => $POSTdata['name']));
            $errors[] = $e->getMessage();
        }
        catch (Exception $e)
        {
            //Přihlášení se nepovedlo kvůli neznámé chybě
            (new Logger(true))->alert('Nebylo možné přihlásit uživatele na IP adresa {ip}, ačkoliv zřejmě zadal správné údaje! Chybová hláška: {exception}', array('ip' => $_SERVER['REMOTE_ADDR'], 'exception' => $e));
            $errors[] = AccessDeniedException::REASON_UNEXPECTED;
        }

        //Je přihlášen úspěšně?
        if (empty($errors) && $userData)
        {
            //Uložit data do $_SESSION
            self::login($userData);

            (new Logger(true))->info('Z IP adresy {ip} se přihlásil uživatel k účtu s ID {userId}', array('ip' => $_SERVER['REMOTE_ADDR'], 'userId' => $userData[LoggedUser::COLUMN_DICTIONARY['id']]));

            //Vygenerovat a uložit token pro trvalé přihlášení
            if ($POSTdata['stayLogged'] === 'true')
            {
                try { self::setLoginCookie($userData[User::COLUMN_DICTIONARY['id']]); }
                catch (Exception $e)
                {
                    (new Logger(true))->error('Nepodařilo se vygenerovat kód pro trvalé přihlášení pro uživatele s ID {userId} přihlašujícího se z IP adresy {ip}', array('userId' => $userData[LoggedUser::COLUMN_DICTIONARY['id']], 'ip' => $_SERVER['REMOTE_ADDR']));
                }
                (new Logger(true))->info('Kód pro trvalé přihlášení byl vygenerován na základě požadavku z IP adresy {ip} a byl přidružen k uživatelskému účtu s ID {userId}', array('ip' => $_SERVER['REMOTE_ADDR'], 'userId' => $userData[User::COLUMN_DICTIONARY['id']]));
            }
        }
        else
        {
            if (empty($errors)) { (new Logger(true))->error('Neznámá chyba při přihlašování uživatele - nebylo možné načíst uživatelská data při požadavku z IP adresy {ip}', array('ip' => $_SERVER['REMOTE_ADDR'])); }
            throw new AccessDeniedException(implode('|', $errors));
        }
    }

    /**
     * Metoda, která se stará o všechny kroky přihlášení pomocí kódu ze souboru cookie pro trvalé přihlášení
     * @param string $code Kód uložený v souboru cookie
     * @throws AccessDeniedException Pokud není kód platný
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     */
    public function processCookieLogin(string $code): void
    {
        //Kontrola správnosti kódu
        $userData = self::verifyCode($code);

        if ($userData)
        {
            self::login($userData);
        }
    }

    /**
     * Metoda ověřující existenci uživatele a správnost hesla.
     * @param string $username Přihlašovací jméno nebo e-mail uživatele
     * @param string $password Přihlašovací heslo
     * @return array Pole s daty o uživateli z databáze v případě úspěchu
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     * @throws AccessDeniedException Pokud uživatel neexistuje nebo heslo nesouhlasí
     */
    private function authenticate(string $username, string $password): array
    {
        if (str_contains($username, '@'))
        {
            //Přihlašování pomocí e-mailu
            $userData = Db::fetchQuery('SELECT * FROM '.User::TABLE_NAME.' WHERE '.LoggedUser::COLUMN_DICTIONARY['email'].' = ? LIMIT 1', array($username), false);
        }
        else
        {
            //Přihlašování pomocí přihlašovacího jména
            $userData = Db::fetchQuery('SELECT * FROM '.User::TABLE_NAME.' WHERE '.LoggedUser::COLUMN_DICTIONARY['name'].' = ? LIMIT 1', array($username), false);
        }
        if ($userData === FALSE){ throw new AccessDeniedException(AccessDeniedException::REASON_LOGIN_NONEXISTANT_USER, null, null); }
        if (!password_verify($password, $userData[LoggedUser::COLUMN_DICTIONARY['hash']])){ throw new AccessDeniedException(AccessDeniedException::REASON_LOGIN_WRONG_PASSWORD, null, null); }
        else { return $userData; }
    }

    /**
     * Metoda kontrolující, zda je v databázi uložen hash kódu obdrženého z instalogin cookie
     * @param string $code Nezahešovaný kód obsažený v souboru cookie
     * @return array|boolean Data o uživateli uložená v databázi, k jehož účtu se lze pomocí daného kódu přihlásit nebo FALSE, pokud je kód neplatný
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     * @throws AccessDeniedException Pokud není kód platný
     */
    private function verifyCode(string $code): array
    {
        $userData = Db::fetchQuery('SELECT * FROM '.User::TABLE_NAME.' WHERE '.LoggedUser::COLUMN_DICTIONARY['id'].' = (SELECT uzivatele_id FROM sezeni WHERE kod_cookie = ? AND expirace > ? LIMIT 1);', array(md5($code), time()), false);
        if ($userData === FALSE) { throw new AccessDeniedException(AccessDeniedException::REASON_LOGIN_INVALID_COOKIE_CODE, null, null); }
        else { return $userData; }
    }

    /**
     * Metoda ukládající data o uživateli z databáze do $_SESSION a aktualizující datum posledního přihlášení v databázi
     * @param array $userData Pole uživatelských dat získaných z databáze
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     */
    private function login(array $userData): void
    {
        $user = new LoggedUser(false, $userData[LoggedUser::COLUMN_DICTIONARY['id']]);
        $user->initialize($userData[LoggedUser::COLUMN_DICTIONARY['name']], $userData[LoggedUser::COLUMN_DICTIONARY['email']], new Datetime(), $userData[LoggedUser::COLUMN_DICTIONARY['addedPictures']], $userData[LoggedUser::COLUMN_DICTIONARY['guessedPictures']], $userData[LoggedUser::COLUMN_DICTIONARY['karma']], $userData[LoggedUser::COLUMN_DICTIONARY['status']], $userData[LoggedUser::COLUMN_DICTIONARY['hash']], $userData[LoggedUser::COLUMN_DICTIONARY['lastChangelog']], $userData[LoggedUser::COLUMN_DICTIONARY['lastMenuTableUrl']]);
        $_SESSION['user'] = $user;

        Db::executeQuery('UPDATE '.User::TABLE_NAME.' SET '.LoggedUser::COLUMN_DICTIONARY['lastLogin'].' = NOW() WHERE '.LoggedUser::COLUMN_DICTIONARY['id'].' = ?', array($userData[LoggedUser::COLUMN_DICTIONARY['id']]));
    }

    /**
     * Metoda generující kód pro cookie trvalého přihlášení a ukládající jej do databáze
     * @param int $userId ID uživatele, s nímž bude kód svázán
     * @throws Exception Pokud se nepovede vygenerovat náhodný kód
     */
    private function setLoginCookie(int $userId): void
    {
        //Vygenerovat čtrnáctimístný kód
        $code = bin2hex(random_bytes(7));   //56 bitů --> maximálně čtrnáctimístný kód

        //Uložit kód do databáze
        try
        {
            Db::executeQuery('INSERT INTO sezeni (kod_cookie, uzivatele_id, expirace) VALUES(?,?,?)', array(md5($code), $userId, time() + self::INSTALOGIN_COOKIE_LIFESPAN));
        }
        catch (DatabaseException $e)
        {
            //Pro případ, že by se vygeneroval již existující kód zopakuj pokus
            self::setLoginCookie($userId);
            return;
        }
        setcookie('instantLogin', $code, time() + self::INSTALOGIN_COOKIE_LIFESPAN, '/');
        $_COOKIE['instantLogin'] = $code;
    }
}

