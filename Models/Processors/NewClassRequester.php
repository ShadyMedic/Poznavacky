<?php
namespace Poznavacky\Models\Processors;

use PHPMailer\PHPMailer\Exception;
use Poznavacky\Models\DatabaseItems\Folder;
use Poznavacky\Models\Emails\EmailComposer;
use Poznavacky\Models\Emails\EmailSender;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Security\DataValidator;
use Poznavacky\Models\Security\NumberAsWordCaptcha;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use InvalidArgumentException;
use RangeException;

/**
 * Kontroler zpracovávající data odeslaná z formuláře pro odeslání žádosti o založení nové třídy
 * Data ověřuje a v případě úspěchu odesílá správci služby e-mail s detaily žádosti
 * @author Jan Štěch
 */
class NewClassRequester
{
    private const WEBMASTER_EMAIL = 'honza.stech@gmail.com';

    /**
     * Metoda zpracovávající data z formuláře a řídící odesílání e-mailu správci služby
     * @param array $POSTdata Data odeslaná z formuláře na request-new-class stránkce
     * @return boolean TRUE, pokud se podařilo odeslat e-mail, FALSE, pokud ne
     * @throws DatabaseException
     * @throws AccessDeniedException Pokud odeslaná data nesplňují podmínky
     * @throws Exception Pokud se nepodaří odeslat webmasterovi e-mail
     */
    public function processFormData(array $POSTdata): bool
    {
        $email = @$POSTdata['email'];
        $name = @$POSTdata['className'];
        $text = @$POSTdata['text'];
        $antispam = @$POSTdata['antispam'];
        
        if ($this->validate($email, $name, $antispam))
        {
            //Kontrola dat v pořádku (jinak by byla vyhozena podmínka)
            
            //Pokud nemusela být zadána e-mailová adresa, získej ji
            if (mb_strlen($email) === 0) { $email = UserManager::getEmail(); }
            
            //Odeslat e-mail
            $composer = new EmailComposer();
            $composer->composeMail(EmailComposer::EMAIL_TYPE_NEW_CLASS_REQUEST, array('username' => UserManager::getName(), 'websiteAddress' => $_SERVER['SERVER_NAME'], 'name' => htmlspecialchars($name), 'message' => nl2br(htmlspecialchars($text)), 'email' => htmlspecialchars($email)));
            
            $sender = new EmailSender();
            return $sender->sendMail(self::WEBMASTER_EMAIL, 'Žádost o založení nové třídy od '.UserManager::getName(), $composer->getMail());
        }
        return false;
    }

    /**
     * Metoda ověřující, zda odeslaná data splňují podmínky
     * @param mixed $email E-mail uživatele, pokud byl zadán
     * @param string $name Požadované jméno nové třídy
     * @param mixed $antispam Odpověď na captchu
     * @return boolean TRUE, pokud jsou všechna data vyplněna správně
     * @throws DatabaseException
     * @throws AccessDeniedException Pokud jsou data vyplněna nesprávně
     */
    private function validate($email, string $name, $antispam): bool
    {
        //Kontrola, zda jsou všechna povinná pole vyplněna
        if (mb_strlen($email) === 0 && empty(UserManager::getEmail()))
        {
            (new Logger(true))->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože nebyl vyplněn kontaktní e-mail', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NO_EMAIL, null, null);
        }
        if (mb_strlen($name) === 0)
        {
            (new Logger(true))->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože nebyl vyplněn název pro třídu', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NO_NAME, null, null);
        }
        if (mb_strlen($antispam) === 0)
        {
            (new Logger(true))->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože nebyla vyplněna ochrana proti robotům', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NO_ANTISPAM, null, null);
        }
        
        //Kontrola formátu e-mailu
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email))
        {
            (new Logger(true))->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože zadaný kontaktní e-mail ({email}) neměl platný formát', array('userId' => UserManager::getId(), 'email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_INVALID_EMAIL, null, null);
        }
        
        $validator = new DataValidator();
        
        //Kontrola délky jména a znaků v něm
        try
        {
            $validator->checkLength($name, DataValidator::CLASS_NAME_MIN_LENGTH, DataValidator::CLASS_NAME_MAX_LENGTH, DataValidator::TYPE_CLASS_NAME);
            $validator->checkCharacters($name, DataValidator::CLASS_NAME_ALLOWED_CHARS, DataValidator::TYPE_CLASS_NAME);
        }
        catch(RangeException $e)
        {
            (new Logger(true))->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože název pro třídu ({className}) má nepřijatelnou délku', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'className' => $name));
            if ($e->getMessage() === 'long') { throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NAME_TOO_LONG, null, $e); }
            else if ($e->getMessage() === 'short') { throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NAME_TOO_SHORT, null, $e); }
        }
        catch(InvalidArgumentException $e)
        {
            (new Logger(true))->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože název pro třídu ({className}) obsahoval nepovolené znaky', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'className' => $name));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NAME_INVALID_CHARACTERS, null, $e);
        }
        
        //Kontrola unikátnosti jména (konkrétně jeho URL reprezentace)
        $url = Folder::generateUrl($name);
        try
        {
            $validator->checkUniqueness($url, DataValidator::TYPE_CLASS_URL);
        }
        catch(InvalidArgumentException $e)
        {
            (new Logger(true))->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože třída s požadovaným názvem ({className}) již existuje', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'className' => $name));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_DUPLICATE_NAME, null, $e);
        }

        //Kontrola, zda URL třídy není rezervované pro žádný kontroler
        try
        {
            $validator->checkForbiddenUrls($url, DataValidator::TYPE_CLASS_URL);
        }
        catch(InvalidArgumentException $e)
        {
            (new Logger(true))->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože URL reprezentace názvu pro třídu ({classUrl}) je rezervovaná pro kontroler', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'classUrl' => $url));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_FORBIDDEN_URL, null, $e);
        }

        //Kontrola antispamu
        $captchaChecker = new NumberAsWordCaptcha();
        if (!$captchaChecker->checkAnswer($antispam, NumberAsWordCaptcha::SESSION_INDEX))
        {
            (new Logger(true))->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože ochrana proti robotům nebyla vyplněna správně', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_CAPTCHA_FAILED, null, null);
        }
        
        return true;
    }
}

