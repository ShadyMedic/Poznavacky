<?php
/**
 * Kontroler zpracovávající data odeslaná z formuláře pro odeslání žádosti o založení nové třídy
 * Data ověřuje a v případě úspěchu odesílá správci služby e-mail s detaily žádosti
 * @author Jan Štěch
 */
class NewClassRequester
{
    private const ADMIN_EMAIL = 'honza.stech@gmail.com';
    
    /**
     * Metoda zpracovávající data z formuláře a řídící odesílání e-mailu správci služby
     * @param array $POSTdata Data odeslaná z formuláře na request-new-class stránkce
     * @throws AccessDeniedException Pokud odeslaná data nesplňují podmínky
     * @return boolean TRUE, pokud se podařilo odeslat e-mail, FALSE, pokud ne
     */
    public function processFormData(array $POSTdata)
    {
        $email = @$POSTdata['email'];
        $name = @$POSTdata['className'];
        $code = @$POSTdata['classCode'];
        $text = @$POSTdata['text'];
        $antispam = @$POSTdata['antispam'];
        
        if ($this->validate($email, $name, $code, $text, $antispam))
        {
            //Kontrola dat v pořádku (jinak by byla vyhozena podmínka)
            
            //Pokud nemusela být zadána e-mailová adresa, získej ji
            if (empty($email)){$email = UserManager::getEmail();}
            
            //Odeslat e-mail
            $composer = new EmailComposer();
            $composer->composeMail(EmailComposer::EMAIL_TYPE_NEW_CLASS_REQUEST, array('username' => UserManager::getName(), 'websiteAddress' => $_SERVER['SERVER_NAME'], 'name' => htmlspecialchars($name), 'code' => htmlspecialchars($code), 'message' => nl2br(htmlspecialchars($text)), 'email' => htmlspecialchars($email)));
            
            $sender = new EmailSender();
            $result = $sender->sendMail(self::ADMIN_EMAIL, 'Žádost o založení nové třídy od '.UserManager::getName(), $composer->getMail());
            
            return $result;
        }
        return false;
    }
    
    /**
     * Metoda ověřující, zda odeslaná data splňují podmínky
     * @param mixed $email E-mail uživatele, pokud byl zadán
     * @param string $name Požadované jméno nové třídy
     * @param mixed $code Požadovaný přístupový kód pro novou třídu
     * @param mixed $text Další informace poskytnuté žadatelem
     * @param mixed $antispam Odpověď na captchu
     * @throws AccessDeniedException Pokud jsou data vyplněna nesprávně
     * @return boolean TRUE, pokud jsou všechna data vyplněna správně
     */
    private function validate($email, string $name, $code, $text, $antispam)
    {
        //Kontrola, zda jsou všechna povinná pole vyplněna
        if (empty($email) && empty(UserManager::getEmail())){throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NO_EMAIL, null, null, array('originFile' => 'NewClassRequester.php', 'displayOnView' => 'requestNewClass.phtml'));}
        if (empty($name)){throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NO_NAME, null, null, array('originFile' => 'NewClassRequester.php', 'displayOnView' => 'requestNewClass.phtml'));}
        if (empty($code)){throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NO_CODE, null, null, array('originFile' => 'NewClassRequester.php', 'displayOnView' => 'requestNewClass.phtml'));}
        if (empty($antispam)){throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NO_ANTISPAM, null, null, array('originFile' => 'NewClassRequester.php', 'displayOnView' => 'requestNewClass.phtml'));}
        
        //Kontrola formátu e-mailu
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)){throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_INVALID_EMAIL, null, null, array('originFile' => 'NewClassRequester.php', 'displayOnView' => 'requestNewClass.phtml'));}
        
        $validator = new DataValidator();
        
        //Kontrola délky a unikátnosti jména
        try
        {
            $validator->checkLength($name, 5, 31, 0);
            $validator->checkUniqueness($name, 3);
        }
        catch(RangeException $e)
        {
            if ($e->getMessage() === 'long'){throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NAME_TOO_LONG, null, $e, array('originFile' => 'NewClassRequester.php', 'displayOnView' => 'requestNewClass.phtml'));}
            else if ($e->getMessage() === 'short'){throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NAME_TOO_SHORT, null, $e, array('originFile' => 'NewClassRequester.php', 'displayOnView' => 'requestNewClass.phtml'));}
        }
        catch(InvalidArgumentException $e){throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_DUPLICATE_NAME, null, $e, array('originFile' => 'NewClassRequester.php', 'displayOnView' => 'requestNewClass.phtml'));}
        
        //Kontrola platnosti kódu
        if (!preg_match('/^\d\d\d\d$/', $code)){throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_INVALID_CODE, null, null, array('originFile' => 'NewClassRequester.php', 'displayOnView' => 'requestNewClass.phtml'));}
        
        //Kontrola antispamu
        $captchaChecker = new NumberAsWordCaptcha();
        if (!$captchaChecker->checkAnswer($antispam, NumberAsWordCaptcha::SESSION_INDEX)){throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_CAPTCHA_FAILED, null, null,  null, null, array('originFile' => 'NewClassRequester.php', 'displayOnView' => 'requestNewClass.phtml'));}
        
        return true;
    }
}