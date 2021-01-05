<?php
namespace Poznavacky\Models\Processors;

use Poznavacky\Models\DatabaseItems\LoggedUser;
use Poznavacky\Models\DatabaseItems\User;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Security\DataValidator;
use \DateTime;
use \InvalidArgumentException;
use \RangeException;
use \RuntimeException;

/** 
 * Třída ověřující uživatelovi registrační údaje a registrující jej
 * @author Jan Štěch
 */
class RegisterUser
{
    const DEFAULT_THEME = 0;
    const DEFAULT_KARMA = 0;
    const DEFAULT_STATUS = User::STATUS_MEMBER;
    
    /**
     * Metoda která se stará o všechny kroky registrace
     * @param array $POSTdata Data odeslaná registračním formulářem, pole s klíči name, pass, repass a email
     * @throws RuntimeException Pokud proces registrace selže
     */
    public function processRegister(array $POSTdata): void
    {
        $name = $POSTdata['name'];
        $pass = $POSTdata['pass'];
        $repass = $POSTdata['repass'];
        $email = $POSTdata['email'];
        
        if (mb_strlen($email) === 0) { $email = null; }
        
        //Ověření dat
        if (self::validateData($name, $pass, $repass, $email))  //Pokud nejsou data v pořádku, je vyhozena výjimka
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
    private function validateData($name, $pass, $repass, $email): bool
    {
        $errors = array();
        $validator = new DataValidator();
        
        //Kontrola existence vyplněných dat
        if (mb_strlen($name) === 0) { $errors[] = AccessDeniedException::REASON_REGISTER_NO_NAME; }
        if (mb_strlen($pass) === 0) { $errors[] = AccessDeniedException::REASON_REGISTER_NO_PASSWORD; }
        if (mb_strlen($repass) === 0) { $errors[] = AccessDeniedException::REASON_REGISTER_NO_REPEATED_PASSWORD; }

        //Pokud není něco vyplněné, nemá smysl pokračovat
        if (!empty($errors))
        {
            throw new AccessDeniedException(implode('|', $errors));
        }

        //Kontrola délky jména, hesla a e-mailu
        try { $validator->checkLength($name, DataValidator::USER_NAME_MIN_LENGTH, DataValidator::USER_NAME_MAX_LENGTH, DataValidator::TYPE_USER_NAME); }
        catch (RangeException $e)
        {
            if ($e->getMessage() === 'long') { $errors[] = AccessDeniedException::REASON_REGISTER_NAME_TOO_LONG; }
            else if ($e->getMessage() === 'short') { $errors[] = AccessDeniedException::REASON_REGISTER_NAME_TOO_SHORT; }
        }
        try { $validator->checkLength($pass, DataValidator::USER_PASSWORD_MIN_LENGTH, DataValidator::USER_PASSWORD_MAX_LENGTH, DataValidator::TYPE_USER_PASSWORD); }
        catch (RangeException $e)
        {
            if ($e->getMessage() === 'long') { $errors[] = AccessDeniedException::REASON_REGISTER_PASSWORD_TOO_LONG; }
            else if ($e->getMessage() === 'short') { $errors[] = AccessDeniedException::REASON_REGISTER_PASSWORD_TOO_SHORT; }
        }
        if (!empty($email))    //Pouze, pokud je e-mail vyplněn
        {
            try { $validator->checkLength($email, DataValidator::USER_EMAIL_MIN_LENGTH, DataValidator::USER_EMAIL_MAX_LENGTH, DataValidator::TYPE_USER_EMAIL); }
            catch (RangeException $e)
            {
                if ($e->getMessage() === 'long') { $errors[] = AccessDeniedException::REASON_REGISTER_EMAIL_TOO_LONG; }
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
                    $errors[] = AccessDeniedException::REASON_REGISTER_NAME_INVALID_CHARACTERS;
                    break;
                case 1:
                    $errors[] = AccessDeniedException::REASON_REGISTER_PASSWORD_INVALID_CHARACTERS;
                    break;
            }
        }

        //Pokud neprošla kontrola na znaky a délku, nemá smysl kontrolovat unikátnost
        if (!empty($errors))
        {
            throw new AccessDeniedException(implode('|', $errors));
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
                    $errors[] = AccessDeniedException::REASON_REGISTER_DUPLICATE_NAME;
                    break;
                case 2:
                    $errors[] = AccessDeniedException::REASON_REGISTER_DUPLICATE_EMAIL;
                    break;
            }
        }
        
        //Kontrola platnosti e-mailu
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email))
        {
            $errors[] = AccessDeniedException::REASON_REGISTER_INVALID_EMAIL;
        }
        
        //Kontrola shodnosti hesel
        if ($pass !== $repass)
        {
            $errors[] = AccessDeniedException::REASON_REGISTER_DIFFERENT_PASSWORDS;
        }

        //Poslední kontrola na chyby
        if (!empty($errors))
        {
            throw new AccessDeniedException(implode('|', $errors));
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
    private function register(string $name, string $password, $email): bool
    {        
        //Uložení dat do databáze
        $password = password_hash($password, PASSWORD_DEFAULT);
        
        $user = new LoggedUser(true);
        $user->initialize($name, $email, new DateTime(), null, null, self::DEFAULT_KARMA, self::DEFAULT_STATUS, $password, null, null, null, self::DEFAULT_THEME);
        $user->save();
        
        //Přihlášení
        $_SESSION['user'] = $user;
        
        //Nastavení cookie pro zabránění přehrávání animace
        $userLogger = new LoginUser();
        $userLogger->setRecentLoginCookie();
        
        return true;
    }
}

