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
        $name = $POSTdata['name'];
        $pass = $POSTdata['pass'];
        $repass = $POSTdata['repass'];
        $email = $POSTdata['email'];
        
        if (mb_strlen($email) === 0){$email = null;}
        
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
        $validator = new DataValidator();
        
        //Kontrola existence vyplněných dat
        if (mb_strlen($name) === 0) { throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_NO_NAME, null, null); }
        if (mb_strlen($pass) === 0) { throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_NO_PASSWORD, null, null); }
        if (mb_strlen($repass) === 0) { throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_NO_REPEATED_PASSWORD, null, null); }
        
        //Kontrola délky jména, hesla a e-mailu
        try
        {
            $validator->checkLength($name, DataValidator::USER_NAME_MIN_LENGTH, DataValidator::USER_NAME_MAX_LENGTH, DataValidator::TYPE_USER_NAME);
            $validator->checkLength($pass, DataValidator::USER_PASSWORD_MIN_LENGTH, DataValidator::USER_PASSWORD_MAX_LENGTH, DataValidator::TYPE_USER_PASSWORD);
            if (!empty($email))    //Pouze, pokud je e-mail vyplněn
            {
                $validator->checkLength($email, DataValidator::USER_EMAIL_MIN_LENGTH, DataValidator::USER_EMAIL_MAX_LENGTH, DataValidator::TYPE_USER_EMAILE);
            }
        }
        catch(RangeException $e)
        {
            if ($e->getMessage() === 'long')
            {
                switch ($e->getCode())
                {
                    case 0:
                        throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_NAME_TOO_LONG, null, $e);
                        break;
                    case 1:
                        throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_PASSWORD_TOO_LONG, null, $e);
                        break;
                    case 2:
                        throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_EMAIL_TOO_LONG, null, $e);
                        break;
                }
            }
            else if ($e->getMessage() === 'short')
            {
                switch ($e->getCode())
                {
                    case 0:
                        throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_NAME_TOO_SHORT, null, $e);
                        break;
                    case 1:
                        throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_PASSWORD_TOO_SHORT, null, $e);
                        break;
                }
            }
        }
        
        //Kontrola znaků ve jméně a hesle
        try
        {
            $validator->checkCharacters($name, DataValidator::USER_NAME_ALLOWED_CHARS, DataValidator::TYPE_USER_NAME);
            $validator->checkCharacters($pass, DataValidator::USER_PASSWORD_ALLOWED_CHARS, DataValidator::TYPE_USER_PASSWORD);
        }
        catch (InvalidArgumentException $e)
        {
            switch ($e->getCode())
            {
                case 0:
                    throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_NAME_INVALID_CHARACTERS, null, $e);
                    break;
                case 1:
                    throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_PASSWORD_INVALID_CHARACTERS, null, $e);
                    break;
            }
        }
        
        //Kontrola unikátnosti jména a e-mailu
        try
        {
            $validator->checkUniqueness($name, DataValidator::TYPE_USER_NAME);
            $validator->checkUniqueness($email, DataValidator::TYPE_USER_EMAIL);
        }
        catch (InvalidArgumentException $e)
        {
            switch ($e->getCode())
            {
                case 0:
                    throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_DUPLICATE_NAME, null, $e);
                    break;
                case 2:
                    throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_DUPLICATE_EMAIL, null, $e);
                    break;
            }
        }
        
        //Kontrola platnosti e-mailu
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_INVALID_EMAIL, null, null);
        }
        
        //Kontrola shodnosti hesel
        if ($pass !== $repass)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_DIFFERENT_PASSWORDS, null, null);
        }
        
        return true;
    }
    
    /**
     * Metoda registrující uživatele do systému po ověření platnosti zadaných dat
     * @param string $name Přezdívka vybraná uživatelem
     * @param string $password Heslo zvolené uživatelem
     * @param string|null $email E-mail zadaný uživatelem (null, pokud žádný nezadal)
     * @return boolean TRUE, pokud je uživatel úspěšně zaregistrován
     */
    private static function register(string $name, string $password, $email)
    {        
        //Uložení dat do databáze
        $password = password_hash($password, PASSWORD_DEFAULT);
        
        $user = new LoggedUser(true);
        $user->initialize($name, $email, new DateTime(), null, null, null, '', $password, null, null, null, null);
        $user->save();
        
        //Přihlášení
        $_SESSION['user'] = $user;
        
        //Nastavení cookie pro zabránění přehrávání animace
        LoginUser::setRecentLoginCookie();
        
        return true;
    }
}