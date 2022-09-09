<?php

namespace Poznavacky\Models\Processors;

use DateTime;
use PHPMailer\PHPMailer\Exception;
use Poznavacky\Models\DatabaseItems\ClassNameChangeRequest;
use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\DatabaseItems\Folder;
use Poznavacky\Models\DatabaseItems\User;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Security\DataValidator;
use Poznavacky\Models\Security\NumberAsWordCaptcha;
use Poznavacky\Models\Statics\Db;
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
    private const TEMP_CLASS_NAME_FORMAT = 'Třída uživatele {userName}';

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
        $email = trim(@$POSTdata['email']); //Ořež mezery
        $name = trim(@$POSTdata['className']); //Ořež mezery
        $tempName = str_replace('{userName}', UserManager::getName(), self::TEMP_CLASS_NAME_FORMAT);
        $antispam = @$POSTdata['antispam'];

        if ($this->validate($email, $name, $tempName, $antispam)) {
            //Kontrola dat v pořádku (jinak by byla vyhozena podmínka)

            //Pokud uživatel nezadal e-mailovou adresu při registraci, doplň mu jí do účtu
            if (mb_strlen($email) !== 0) {
                $userId = UserManager::getId();
                Db::executeQuery('UPDATE '.User::TABLE_NAME.' 
                SET '.User::COLUMN_DICTIONARY['email'].' = ? 
                WHERE '.User::COLUMN_DICTIONARY['id'].' = ?', array($email, $userId));

                //Nastav e-mailovou adresu i v proměnné uložené v sezení
                $user = UserManager::getUser();
                $user->initialize(null, $email);
            }

            //Vytvoř novou třídu s automatickým názvem
            $newClassId = Db::executeQuery('INSERT INTO '.ClassObject::TABLE_NAME.'(
                '.ClassObject::COLUMN_DICTIONARY['name'].','.ClassObject::COLUMN_DICTIONARY['url'].','.ClassObject::COLUMN_DICTIONARY['admin'].'
                ) VALUES (?,?,?);', array($tempName, ClassObject::generateUrl($tempName), UserManager::getId()), true);

            //Dej správci členství
            Db::executeQuery('INSERT INTO clenstvi(uzivatele_id,tridy_id) VALUES (?,?);',
                array(UserManager::getId(), $newClassId));

            if (UserManager::getOtherInformation()['status'] !== User::STATUS_ADMIN) {
                //Změň správcův status (pokud není správcem systému)
                Db::executeQuery('UPDATE '.User::TABLE_NAME.' 
                    SET '.User::COLUMN_DICTIONARY['status'].' = ? 
                    WHERE '.User::COLUMN_DICTIONARY['id'].' = ?;',
                    array(User::STATUS_CLASS_OWNER, UserManager::getId()));
                //Změň status uživatele i v sezení
                $user = UserManager::getUser();
                $user->initialize(null, null, null, null, null, null, User::STATUS_CLASS_OWNER);
            }

            //Vytvoř žádost o změnu názvu třídy
            return Db::executeQuery('INSERT INTO '.ClassNameChangeRequest::TABLE_NAME.'(
                '.ClassNameChangeRequest::COLUMN_DICTIONARY['subject'].','.ClassNameChangeRequest::COLUMN_DICTIONARY['newName'].','.ClassNameChangeRequest::COLUMN_DICTIONARY['newUrl'].','.ClassNameChangeRequest::COLUMN_DICTIONARY['requestedAt'].'
                ) VALUES (?,?,?,?)', array($newClassId, $name, ClassObject::generateUrl($name), (new DateTime('now'))->format('Y-m-d H:i:s')));
        }
        return false;
    }

    /**
     * Metoda ověřující, zda odeslaná data splňují podmínky
     * @param mixed $email E-mail uživatele, pokud byl zadán
     * @param string $name Požadovaný název nové třídy
     * @param string $tempName Název, který bude třída mít před schválením požadovaného názvu administrátorem
     * @param mixed $antispam Odpověď na captchu
     * @return boolean TRUE, pokud jsou všechna data vyplněna správně
     * @throws DatabaseException
     * @throws AccessDeniedException Pokud jsou data vyplněna nesprávně
     */
    private function validate($email, string $name, string $tempName, $antispam): bool
    {
        //Kontrola, zda jsou všechna povinná pole vyplněna
        if (mb_strlen($email) === 0 && empty(UserManager::getEmail())) {
            (new Logger())->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože nebyla vyplněna e-mailová adresa',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NO_EMAIL, null, null);
        }
        if (mb_strlen($name) === 0) {
            (new Logger())->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože nebyl vyplněn název pro třídu',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NO_NAME, null, null);
        }
        if (mb_strlen($antispam) === 0) {
            (new Logger())->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože nebyla vyplněna ochrana proti robotům',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NO_ANTISPAM, null, null);
        }

        //Kontrola formátu e-mailu
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
            (new Logger())->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože zadaná e-mailová adresa ({email}) neměla platný formát',
                array('userId' => UserManager::getId(), 'email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_INVALID_EMAIL, null, null);
        }

        $validator = new DataValidator();

        //Kontrola délky jména a znaků v něm
        try {
            $validator->checkLength($name, DataValidator::CLASS_NAME_MIN_LENGTH, DataValidator::CLASS_NAME_MAX_LENGTH,
                DataValidator::TYPE_CLASS_NAME);
            $validator->checkCharacters($name, DataValidator::CLASS_NAME_ALLOWED_CHARS, DataValidator::TYPE_CLASS_NAME);
        } catch (RangeException $e) {
            (new Logger())->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože název pro třídu ({className}) má nepřijatelnou délku',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'className' => $name));
            if ($e->getMessage() === 'long') {
                throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NAME_TOO_LONG, null,
                    $e);
            } else {
                if ($e->getMessage() === 'short') {
                    throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NAME_TOO_SHORT,
                        null, $e);
                }
            }
        } catch (InvalidArgumentException $e) {
            (new Logger())->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože název pro třídu ({className}) obsahoval nepovolené znaky',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'className' => $name));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_NAME_INVALID_CHARACTERS,
                null, $e);
        }

        //Kontrola unikátnosti požadovaného názvu třídy (konkrétně jeho URL reprezentace)
        $url = Folder::generateUrl($name);
        try {
            $validator->checkUniqueness($url, DataValidator::TYPE_CLASS_URL);
        } catch (InvalidArgumentException $e) {
            (new Logger())->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože třída s požadovaným názvem ({className}) již existuje',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'className' => $name));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_DUPLICATE_NAME, null, $e);
        }

        //Kontrola unikátnosti dočasného názvu třídy (konkrétně jeho URL reprezentace)
        $tempUrl = Folder::generateUrl($tempName);
        try {
            $validator->checkUniqueness($tempUrl, DataValidator::TYPE_CLASS_URL);
        } catch (InvalidArgumentException $e) {
            (new Logger())->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože třída s dočasným názvem pro tohoto uživatele již existuje',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_ALREADY_WAITING, null, $e);
        }

        //Kontrola, zda URL třídy není rezervované pro žádný kontroler
        try {
            $validator->checkForbiddenUrls($url, DataValidator::TYPE_CLASS_URL);
        } catch (InvalidArgumentException $e) {
            (new Logger())->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože URL reprezentace názvu pro třídu ({classUrl}) je rezervovaná pro kontroler',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'classUrl' => $url));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_FORBIDDEN_URL, null, $e);
        }

        //Kontrola antispamu
        $captchaChecker = new NumberAsWordCaptcha();
        if (!$captchaChecker->checkAnswer($antispam, NumberAsWordCaptcha::SESSION_INDEX)) {
            (new Logger())->notice('Pokus o odeslání formuláře pro založení nové třídy uživatelem s ID {userId} z IP adresy {ip} selhal, protože ochrana proti robotům nebyla vyplněna správně',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(AccessDeniedException::REASON_NEW_CLASS_REQUEST_CAPTCHA_FAILED, null, null);
        }

        return true;
    }
}

