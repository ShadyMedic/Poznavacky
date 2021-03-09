<?php
namespace Poznavacky\Controllers;

use PHPMailer\PHPMailer\Exception;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Processors\LoginUser;
use Poznavacky\Models\Processors\RecoverPassword;
use Poznavacky\Models\Processors\RegisterUser;
use Poznavacky\Models\Security\DataValidator;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\Logger;
use \InvalidArgumentException;

/**
 * Kontroler zpracovávající data z formulářů na index stránce
 * (přihlášení, registrace, obnova hesla)
 * Kontroler je volán pomocí AJAX požadavku z index.js
 * @author Jan Štěch
 */
class IndexFormsController extends AjaxController
{
    /**
     * Metoda přijímající data z formulářů skrz $_POST a volající model, který je zpracuje.
     * Podle výsledku zpracování dat odesílá instrukce k přesměrování na menu stránku nebo odesílá chybovou hlášku.
     * V případě, že se během zpracovávání dat narazilo na větší množství chyb, jsou v odpovědi odděleny svislítkem ("|")
     * @param array $parameters Parametry pro kontroler (nevyužíváno)
     * @see AjaxController::process()
     */
    public function process(array $parameters): void
    {
        header('Content-Type: application/json');
        try
        {
            $type = $_POST['type'];
            switch($type)
            {
                //Kontrola unikátnosti přihlašovacího jména nebo e-mailové adresy
                case 'u':
                case 'e':
                    $form = 'unknown';
                    $string = $_POST['text'];
                    $stringType = ($_POST['type'] === 'u' && !str_contains($string, '@')) ? DataValidator::TYPE_USER_NAME : DataValidator::TYPE_USER_EMAIL;
                    $validator = new DataValidator();
                    try
                    {
                        $validator->checkUniqueness($string, $stringType);
                        $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, '', array('unique' => true));
                        (new Logger(true))->info('Kontrola unikátnosti řetězce {string} z IP adresy {ip} prošla', array('string' => $string, 'ip' => $_SERVER['REMOTE_ADDR']));
                    }
                    catch (InvalidArgumentException $e)
                    {
                        $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, '', array('unique' => false));
                        (new Logger(true))->info('Kontrola unikátnosti řetězce {string} z IP adresy {ip} neprošla', array('string' => $string, 'ip' => $_SERVER['REMOTE_ADDR']));
                    }
                    echo $response->getResponseString();
                    break;
                //Přihlašování
                case 'l':
                    $form = 'login';
                    $userLogger = new LoginUser();
                    $userLogger->processLogin($_POST);
                    $menuUrl = 'menu';
                    if (!empty(UserManager::getUser()['lastMenuTableUrl'])) { $menuUrl .= '/'.UserManager::getUser()['lastMenuTableUrl']; }
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_REDIRECT, $menuUrl);
                    echo $response->getResponseString();
                    break;
                //Registrace
                case 'r':
                    $form = 'register';
                    $userRegister = new RegisterUser();
                    $userRegister->processRegister($_POST);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_REDIRECT, 'menu');
                    echo $response->getResponseString();
                    break;
                //Obnova hesla
                case 'p':
                    $form = 'passRecovery';
                    $passwordRecoverer = new RecoverPassword();
                    try
                    {
                        $passwordRecoverer->processRecovery($_POST);
                        $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Na vámi zadanou e-mailovou adresu byly odeslány další instrukce pro obnovu hesla. Pokud vám e-mail nepřišel, zkontrolujte prosím i složku se spamem a/nebo opakujte akci. V případě dlouhodobých problémů prosíme kontaktujte správce.', array('origin' => $form));
                    }
                    catch (Exception $e)
                    {
                        $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, 'E-mail pro obnovu hesla se nepovedlo odeslat. Kontaktujte prosím administrátora, nebo zkuste akci opakovat později.', array('origin' => $form));
                    }
                    echo $response->getResponseString();
                    break;
            }
        }
        catch (AccessDeniedException $e)
        {
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage(), array('origin' => $form));
            echo $response->getResponseString();
        }
        catch (DatabaseException $e)
        {
            (new Logger(true))->emergency('Po odeslání dat z formuláře na index stránce uživatelem na IP adrese {ip} se vyskytla chyba databáze; je možné, že se k databázi není možné vůbec připojit a celý systém je tak nepoužitelný! Text výjimky: {exception}', array('ip' => $_SERVER['REMOTE_ADDR'], 'exception' => $e));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, AccessDeniedException::REASON_UNEXPECTED, array('origin' => $form));
            echo $response->getResponseString();
        }
    }
}

