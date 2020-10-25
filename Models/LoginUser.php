<?php
/** 
 * Třída ověřující uživatelovi přihlašovací údaje a přihlašující jej
 * @author Jan Štěch
 */
class LoginUser
{
    //Čas po jaký není nutné znovu přidávat heslo, pokud je při přihlášení zaškrtnuto políčko "Zůstat přihlášen"
    private const INSTALOGIN_COOKIE_LIFESPAN = 2592000;    //2 592 000‬ s = 30 dní
    private const RECENTLOGIN_COOKIE_LIFESPAN = 28800;     //28 800 s = 8 hodin
    
    /**
     * Metoda která se stará o všechny kroky přihlašování
     * @param array $POSTdata Data odeslaná přihlašovacím formulářem, pole s klíči loginName, loginPass a popřípadě stay_logged
     */
    public static function processLogin(array $POSTdata)
    {
        //Ověřit vyplněnost dat
        if (mb_strlen($POSTdata['name']) === 0){ throw new AccessDeniedException(AccessDeniedException::REASON_LOGIN_NO_NAME, null, null, array('originFile' => 'LoginUser.php', 'displayOnView' => 'index.phtml', 'form' => 'login')); }
        if (mb_strlen($POSTdata['pass']) === 0){ throw new AccessDeniedException(AccessDeniedException::REASON_LOGIN_NO_PASSWORD, null, null, array('originFile' => 'LoginUser.php', 'displayOnView' => 'index.phtml', 'form' => 'login')); }
        
        //Pokusit se přihlásit
        $userData = self::authenticate($POSTdata['name'], $POSTdata['pass']);
        
        //Je přihlášen úspěšně?
        if ($userData)
        {
            //Uložit data do $_SESSION
            self::login($userData);
            
            //Vygenerovat a uložit token pro trvalé přihlášení
            if ($POSTdata['stayLogged'] === 'true')
            {
                self::setLoginCookie($userData[User::COLUMN_DICTIONARY['id']]);
            }
        }
    }
    
    /**
     * Metoda, která se stará o všechny kroky přihlášení pomocí kódu ze souboru cookie pro trvalé přihlášení
     * @param string $code Kód uložený v souboru cookie
     */
    public static function processCookieLogin(string $code)
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
     * @param string $username
     * @param string $password
     * @throws AccessDeniedException Pokud uživatel neexistuje nebo heslo nesouhlasí
     * @return array|boolean Pole s daty o uživateli z databáze v případě úspěchu
     */
    private static function authenticate(string $username, string $password)
    {
        Db::connect();
        $userData = Db::fetchQuery('SELECT * FROM '.User::TABLE_NAME.' WHERE '.LoggedUser::COLUMN_DICTIONARY['name'].' = ? LIMIT 1', array($username), false);
        if ($userData === FALSE){ throw new AccessDeniedException(AccessDeniedException::REASON_LOGIN_NONEXISTANT_USER, null, null, array('originFile' => 'LoginUser.php', 'displayOnView' => 'index.phtml', 'form' => 'login')); }
        if (!password_verify($password, $userData[LoggedUser::COLUMN_DICTIONARY['hash']])){ throw new AccessDeniedException(AccessDeniedException::REASON_LOGIN_WRONG_PASSWORD, null, null, array('originFile' => 'LoginUser.php', 'displayOnView' => 'index.phtml', 'form' => 'login')); }
        else {return $userData;}
        return false;
    }
    
    /**
     * Metoda kontrolující, zda je v databázi uložen hash kódu obdrženého z instalogin cookie
     * @param string $code Nezahešovaný kód obsažený v souboru cookie
     * @throws AccessDeniedException Pokud není kód platný
     * @return array|boolean Data o uživateli uložená v databázi, k jehož účtu se lze pomocí daného kódu přihlásit nebo FALSE, pokud je kód neplatný
     */
    private static function verifyCode(string $code)
    {
        Db::connect();
        $userData = Db::fetchQuery('SELECT * FROM '.User::TABLE_NAME.' WHERE '.LoggedUser::COLUMN_DICTIONARY['id'].' = (SELECT uzivatele_id FROM sezeni WHERE kod_cookie = ? LIMIT 1);', array(md5($code)), false);
        if ($userData === FALSE) {throw new AccessDeniedException(AccessDeniedException::REASON_LOGIN_INVALID_COOKIE_CODE, null, null, array('originFile' => 'LoginUser.php', 'displayOnView' => 'index.phtml', 'form' => 'login'));}
        else {return $userData;}
        return false;
    }
    
    /**
     * Metoda ukládající data o uživateli z databáze do $_SESSION a aktualizující datum posledního přihlášení v databázi
     * @param array $userData
     */
    private static function login(array $userData)
    {
        $user = new LoggedUser(false, $userData[LoggedUser::COLUMN_DICTIONARY['id']]);
        $user->initialize($userData[LoggedUser::COLUMN_DICTIONARY['name']], $userData[LoggedUser::COLUMN_DICTIONARY['email']], new Datetime(), $userData[LoggedUser::COLUMN_DICTIONARY['addedPictures']], $userData[LoggedUser::COLUMN_DICTIONARY['guessedPictures']], $userData[LoggedUser::COLUMN_DICTIONARY['karma']], $userData[LoggedUser::COLUMN_DICTIONARY['status']], $userData[LoggedUser::COLUMN_DICTIONARY['hash']], $userData[LoggedUser::COLUMN_DICTIONARY['lastChangelog']], $userData[LoggedUser::COLUMN_DICTIONARY['lastLevel']], $userData[LoggedUser::COLUMN_DICTIONARY['lastFolder']], $userData[LoggedUser::COLUMN_DICTIONARY['theme']]);
        $_SESSION['user'] = $user;
        
        Db::executeQuery('UPDATE '.User::TABLE_NAME.' SET '.LoggedUser::COLUMN_DICTIONARY['lastLogin'].' = NOW() WHERE '.LoggedUser::COLUMN_DICTIONARY['id'].' = ?', array($userData[LoggedUser::COLUMN_DICTIONARY['id']]));
        
        //Nastavení cookie pro zabránění přehrávání animace
        self::setRecentLoginCookie();
    }
    
    /**
     * Metoda generující kód pro cookie trvalého přihlášení a ukládající jej do databáze
     * @param int $userId ID uživatele, s nímž bude kód svázán
     */
    private static function setLoginCookie(int $userId)
    {
        //Vygenerovat čtrnáctimístný kód
        $code = bin2hex(random_bytes(7));   //56 bitů --> maximálně čtrnáctimístný kód
        
        //Uložit kód do databáze
        try
        {
            Db::executeQuery('INSERT INTO sezeni (kod_cookie, uzivatele_id) VALUES(?,?)', array(md5($code), $userId));
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
    
    /**
     * Metoda nastavující cookie indukující, že se z tohoto počítače nedávno přihlásil nějaký uživatel a zabraňuje tak přehrávání animace na index stránce
     * Metoda je využívána i modelem Register.php a kontrolerem LogoutController.php
     */
    public static function setRecentLoginCookie()
    {
        setcookie('recentLogin', true, time() + self::RECENTLOGIN_COOKIE_LIFESPAN, '/');
    }
}

