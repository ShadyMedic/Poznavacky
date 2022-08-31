<?php
namespace Poznavacky\Controllers\Menu\Management\Account;

use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\Logger;

/**
 * Kontroler zpracovávající data odeslaná ze stránky account-settings
 * @author Jan Štěch
 */
class AccountUpdateController extends AjaxController
{
    /**
     * Metoda odlišující, jaká data si přeje uživatel změnit a volající příslušný model
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws DatabaseException
     * @see AjaxController::process()
     */
    public function process(array $parameters): void
    {
        if (!isset($_POST['action'])) {
            header('HTTP/1.0 400 Bad Request');
            return;
        }
        
        header('Content-Type: application/json');
        try {
            switch ($_POST['action']) {
                case 'request name change':
                    $newName = trim(urldecode($_POST['name'])); //Ořež mezery
                    $user = UserManager::getUser();
                    $user->requestNameChange($newName);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS,
                        'Žádost o změnu jména byla odeslána. Sledujte prosím svou e-mailovou schránku (pokud jste si zde nastavili e-mailovou adresu). V okamžiku, kdy vaši změnu posoudí správce, dostanete zprávu.',
                        array('origin' => $_POST['action']));
                    echo $response->getResponseString();
                    break;
                case 'change password':
                    $oldPassword = urldecode($_POST['oldPassword']);
                    $newPassword = urldecode($_POST['newPassword']);
                    $rePassword = urldecode($_POST['rePassword']);
                    $user = UserManager::getUser();
                    $user->changePassword($oldPassword, $newPassword, $rePassword);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Heslo bylo úspěšně změněno',
                        array('origin' => $_POST['action']));
                    echo $response->getResponseString();
                    break;
                case 'change email':
                    $password = urldecode($_POST['password']);
                    $email = trim(urldecode($_POST['newEmail'])); //Ořež mezery
                    $user = UserManager::getUser();
                    $user->changeEmail($password, $email);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'E-mail byl úspěšně změněn',
                        array('origin' => $_POST['action']));
                    echo $response->getResponseString();
                    break;
                case 'delete account':
                    $password = urldecode($_POST['password']);
                    $user = UserManager::getUser();
                    $user->deleteAccount($password);
                    unset($user);
                    //Naposled přesměruj uživatele ven ze systému
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_REDIRECT, '');
                    echo $response->getResponseString();
                    break;
                case 'verify password':
                    $password = urldecode($_POST['password']);
                    if (mb_strlen($password) === 0) {
                        (new Logger())->notice('Prohlížeč uživatele s ID {userId} se odeslal požadavek na ověření hesla z IP adresy {ip}, avšak žádné heslo ke kontrole nebylo odesláno',
                            array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
                        throw new AccessDeniedException(AccessDeniedException::REASON_NO_PASSWORD_GENERAL);
                    }
                    $aChecker = new AccessChecker();
                    if (!$aChecker->recheckPassword($password)) {
                        (new Logger())->info('Prohlížeč uživatele s ID {userId} se odeslal požadavek na ověření hesla z IP adresy {ip}, které bylo vyhodnoceno jako nesprávné',
                            array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
                        throw new AccessDeniedException(AccessDeniedException::REASON_WRONG_PASSWORD_GENERAL);
                    }
                    (new Logger())->notice('Prohlížeč uživatele s ID {userId} se odeslal požadavek na ověření hesla z IP adresy {ip}, které bylo vyhodnoceno jako správné',
                        array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, '', array('verified' => true));
                    echo $response->getResponseString();
                    break;
                default:
                    header('HTTP/1.0 400 Bad Request');
                    return;
            }
        } catch (AccessDeniedException $e) {
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage(),
                array('origin' => $_POST['action']));
            echo $response->getResponseString();
        }
    }
}

