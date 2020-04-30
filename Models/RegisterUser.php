<?php
/** 
 * Třída ověřující uživatelovi registrační údaje a registrující jej
 * @author Jan Štěch
 */
class RegisterUser
{
    const DEFAULT_THEME = 0;
    const DEFAULT_KARMA = 0;
    const DEFAULT_STATUS = User::STATUS_MEMBER;
    
    public static function processRegister(array $POSTdata)
    {
        $name = $POSTdata['registerName'];
        $pass = $POSTdata['registerPass'];
        $repass = $POSTdata['registerRepass'];
        $email = $POSTdata['registerEmail'];
        
        if (empty($email)){$email = null;}
        
        //Ověření dat
        if (self::validateData($name, $pass, $repass, $email))
        {
            if (!self::register($name, $pass, $email))
            {
                throw new RuntimeException('Uživatele se nepovedlo zaregistrovat. Zkuste to prosím znovu později', null, null);
            }
        }
    }
    
    /**
     * Metoda ověřující, zda data zadáná do formuláře splňují podmínky
     * @param string $name Zadané jméno uživatele
     * @param string $pass Zadané heslo uživatele
     * @param string $repass Zadané opakované heslo uživatele
     * @param string $email Zadaný e-mail uživatele (null, pokud nebyl zadán)
     * @throws AccessDeniedException Pokud některý z údajů nesplňuje podmínky
     * @return boolean TRUE, pokud všechny údaje splňují podmínky
     */
    private static function validateData($name, $pass, $repass, $email)
    {
        //Kontrola existence vyplněných dat
        if (!isset($name)) { throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_NO_NAME, null, null, array('originalFile' => 'RegisterUser.php', 'displayOnView' => 'index.phtml', 'form' => 'register')); }
        if (!isset($pass)) { throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_NO_PASSWORD, null, null, array('originalFile' => 'RegisterUser.php', 'displayOnView' => 'index.phtml', 'form' => 'register')); }
        if (!isset($repass)) { throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_NO_REPEATED_PASSWORD, null, null, array('originalFile' => 'RegisterUser.php', 'displayOnView' => 'index.phtml', 'form' => 'register')); }
        
        //Kontrola délky jména, hesla a e-mailu
        try
        {
            self::checkLength($name, 4, 15, 0);
            self::checkLength($pass, 6, 31, 1);
            self::checkLength($email, 0, 255, 2);
        }
        catch(RangeException $e)
        {
            if ($e->getMessage() === 'long')
            {
                switch ($e->getCode())
                {
                    case 0:
                        throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_NAME_TOO_LONG, null, $e, array('originalFile' => 'RegisterUser.php', 'displayOnView' => 'index.phtml', 'form' => 'register'));
                        break;
                    case 1:
                        throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_PASSWORD_TOO_LONG, null, $e, array('originalFile' => 'RegisterUser.php', 'displayOnView' => 'index.phtml', 'form' => 'register'));
                        break;
                    case 2:
                        throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_EMAIL_TOO_LONG, null, $e, array('originalFile' => 'RegisterUser.php', 'displayOnView' => 'index.phtml', 'form' => 'register'));
                        break;
                }
            }
            else if ($e->getMessage() === 'short')
            {
                switch ($e->getCode())
                {
                    case 0:
                        throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_NAME_TOO_SHORT, null, $e, array('originalFile' => 'RegisterUser.php', 'displayOnView' => 'index.phtml', 'form' => 'register'));
                        break;
                    case 1:
                        throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_PASSWORD_TOO_SHORT, null, $e, array('originalFile' => 'RegisterUser.php', 'displayOnView' => 'index.phtml', 'form' => 'register'));
                        break;
                }
            }
        }
        
        //Kontrola znaků ve jméně a hesle
        try
        {
            self::checkCharacters($name, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ ', 0);
            self::checkCharacters($pass, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ {}()[]#:;^,.?!|_`~@$%/+-*=\"\'', 1);
        }
        catch (InvalidArgumentException $e)
        {
            switch ($e->getCode())
            {
                case 0:
                    throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_NAME_INVALID_CHARACTERS, null, $e, array('originalFile' => 'RegisterUser.php', 'displayOnView' => 'index.phtml', 'form' => 'register'));
                    break;
                case 1:
                    throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_PASSWORD_INVALID_CHARACTERS, null, $e, array('originalFile' => 'RegisterUser.php', 'displayOnView' => 'index.phtml', 'form' => 'register'));
                    break;
            }
        }
        
        //Kontrola unikátnosti jména a e-mailu
        try
        {
            self::checkUniqueness($name, 0);
            self::checkUniqueness($email, 2);
        }
        catch (InvalidArgumentException $e)
        {
            switch ($e->getCode())
            {
                case 0:
                    throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_DUPLICATE_NAME, null, $e, array('originalFile' => 'RegisterUser.php', 'displayOnView' => 'index.phtml', 'form' => 'register'));
                    break;
                case 2:
                    throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_DUPLICATE_EMAIL, null, $e, array('originalFile' => 'RegisterUser.php', 'displayOnView' => 'index.phtml', 'form' => 'register'));
                    break;
            }
        }
        
        //Kontrola platnosti e-mailu
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_INVALID_EMAIL, null, null, array('originalFile' => 'RegisterUser.php', 'displayOnView' => 'index.phtml', 'form' => 'register'));
        }
        
        //Kontrola shodnosti hesel
        if ($pass !== $repass)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_DIFFERENT_PASSWORDS, null, null, array('originalFile' => 'RegisterUser.php', 'displayOnView' => 'index.phtml', 'form' => 'register'));
        }
        
        return true;
    }
    
    /**
     * Metoda ověřující, zda se délka řetězce nachází mezi minimální a maximální hodnotou.
     * @param string $subject Řetězec, jehož délku ověřujeme
     * @param int $min Minimální povolená délka řetězce (včetně)
     * @param int $max Maximální povolená délka řetězce (včetně)
     * @param int $stringType Označení porovnávaného řetězce (pro rozlišení výjimek) - 0 pro jméno, 1 pro heslo, 2 pro e-mail
     * @throws LengthException Pokud délka řetězce nespadá mezi $min a $max. Zpráva výjimky je 'long' nebo 'short' podle toho, jaká hranice byla přesažena
     * @return boolean TRUE, pokud délka řetězce spadá mezi $min a $max
     */
    private static function checkLength($subject, int $min, int $max, int $stringType = null)
    {
        if ($stringType === 2 && empty($subject))
        {
            //Nevyplněný e-mail
            return true;
        }
        
        if (mb_strlen($subject) > $max)
        {
            throw new RangeException('long', $stringType);
        }
        if (mb_strlen($subject) < $min)
        {
            throw new RangeException('short', $stringType);
        }
        return true;
    }
    
    /**
     * Metoda ověřující, zda se řetězec skládá pouze z povolených znaků
     * @param string $subject Řetězec, jehož znaky ověřujeme
     * @param string $allowedChars Řetězec skládající se z výčtu všech povolených znaků
     * @param int $stringType Označení porovnávaného řetězce (pro rozlišení výjimek) - 0 pro jméno, 1 pro heslo
     * @throws InvalidArgumentException Pokud se řetězec skládá i z jiných než povolených znaků
     * @returns boolean TRUE, pokud se řetězec skládá pouze z povolených znaků
     */
    private static function checkCharacters(string $subject, string $allowedChars, int $stringType = null)
    {
        if(mb_strlen($subject) !== strspn($subject, $allowedChars))
        {
            throw new InvalidArgumentException(null, $stringType);
        }
        return true;
    }
    
    /**
     * Metoda ověřující, zda se již řetězec v databázi (v tabulce uzivatele) nevyskytuje
     * @param string $subject Řetězec jehož unikátnost chceme zjistit
     * @param int $stringType Označení porovnávaného řetězce (pro rozlišení výjimek) - 0 pro jméno, 2 pro e-mail
     * @throws InvalidArgumentException Pokud se již řetězec v databázi vyskytuje
     * @return boolean TRUE, pokud se řetězec zatím v databázi nevyskytuje
     */
    private static function checkUniqueness($subject, int $stringType)
    {
        Db::connect();
        switch ($stringType)
        {   
            case 0:
                $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM uzivatele WHERE jmeno = ?', array($subject), false);
                if ($result['cnt'] > 0)
                {
                    throw new InvalidArgumentException(null, $stringType);
                }
                break;
            case 2:
                if (empty($subject))
                {
                    //Nevyplněný e-mail
                    return true;
                }
                $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM uzivatele WHERE email = ?', array($subject), false);
                if ($result['cnt'] > 0)
                {
                    throw new InvalidArgumentException(null, $stringType);
                }
                break;
        }
        return true;
    }
    
    /**
     * Metoda registrující uživatele do systému po ověření platnosti zadaných dat
     * @param string $name
     * @param string $password
     * @param string $email
     * @return boolean TRUE, pokud je uživatel úspěšně zaregistrován
     */
    private static function register(string $name, string $password, $email)
    {
        Db::connect();
        
        //Uložení dat do databáze
        $password = password_hash($password, PASSWORD_DEFAULT);
        Db::executeQuery('INSERT INTO uzivatele (jmeno, heslo, email, posledni_prihlaseni) VALUES (?,?,?,?)', array($name, $password, $email, date('Y-m-d H:i:s')));
        
        //Přihlášení
        $id = Db::fetchQuery('SELECT uzivatele_id FROM uzivatele WHERE jmeno=? LIMIT 1', array($name), false);
        $id = $id['uzivatele_id'];
        
        session_start();
        $user = new LoggedUser($id, $name, $password, $email, new DateTime(null, new DateTimeZone('EUROPE/PRAGUE')), 0, 0, null, self::DEFAULT_THEME, 0, 0, self::DEFAULT_KARMA, self::DEFAULT_STATUS);
        $_SESSION['user'] = $user;
        
        return true;
    }
}