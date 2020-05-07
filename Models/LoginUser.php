<?php
/** 
 * Třída ověřující uživatelovi přihlašovací údaje a přihlašující jej
 * @author Jan Štěch
 */
class LoginUser
{
    //Čas po jaký není nutné znovu přidávat heslo, pokud je při přihlášení zaškrtnuto políčko "Zůstat přihlášen"
    private const INSTALOGIN_COOKIE_LIFESPAN = 2592000;    //2 592 000‬ s = 30 dní
    
    /**
     * Metoda která se stará o všechny kroky přihlašování
     * @param array $POSTdata Data odeslaná přihlašovacím formulářem, pole s klíči loginName, loginPass a popřípadě stay_logged
     */
    public static function processLogin(array $POSTdata)
    {
        //Ověřit vyplněnost dat
        if (!isset($POSTdata['loginName'])){ throw new AccessDeniedException(AccessDeniedException::REASON_LOGIN_NO_NAME, null, null, array('originFile' => 'LoginUser.php', 'displayOnView' => 'index.phtml', 'form' => 'login')); }
        if (!isset($POSTdata['loginPass'])){ throw new AccessDeniedException(AccessDeniedException::REASON_LOGIN_NO_PASSWORD, null, null, array('originFile' => 'LoginUser.php', 'displayOnView' => 'index.phtml', 'form' => 'login')); }
        
        //Pokusit se přihlásit
        $userData = self::authenticate($POSTdata['loginName'], $POSTdata['loginPass']);
        
        //Je přihlášen úspěšně?
        if ($userData)
        {
            //Uložit data do $_SESSION
            self::login($userData);
            
            //Vygenerovat a uložit token pro trvalé přihlášení
            if (isset($POSTdata['stay_logged']))
            {
                self::setLoginCookie($userData['uzivatele_id']);
            }
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
        $userData = Db::fetchQuery('SELECT * FROM uzivatele WHERE jmeno = ? LIMIT 1', array($username), false);
        if ($userData === FALSE){ throw new AccessDeniedException(AccessDeniedException::REASON_LOGIN_NONEXISTANT_USER, null, null, array('originFile' => 'LoginUser.php', 'displayOnView' => 'index.phtml', 'form' => 'login')); }
        if (!password_verify($password, $userData['heslo'])){ throw new AccessDeniedException(AccessDeniedException::REASON_LOGIN_WRONG_PASSWORD, null, null, array('originFile' => 'LoginUser.php', 'displayOnView' => 'index.phtml', 'form' => 'login')); }
        else {return $userData;}
        return false;
    }
    
    /**
     * Metoda ukládající data o uživateli z databáze do $_SESSION
     * @param array $userData
     */
    private static function login(array $userData)
    {
        $user = new LoggedUser($userData['uzivatele_id'], $userData['jmeno'], $userData['heslo'], $userData['email'], new Datetime($userData['posledni_prihlaseni']), $userData['posledni_changelog'], $userData['posledni_uroven'], $userData['posledni_slozka'], $userData['vzhled'], $userData['pridane_obrazky'], $userData['uhodnute_obrazky'], $userData['karma'], $userData['status']);
        $_SESSION['user'] = $user;
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
}

