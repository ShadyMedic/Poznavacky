<?php
/** 
 * Kontroler zpracovávající data odeslaná ze stránky account-settings
 * @author Jan Štěch
 */
class AccountUpdateController extends Controller
{
    /**
     * Metoda odlišující, jaká data si přeje uživatel změnit a volající příslušný model
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        if (empty($_POST))
        {
            header('HTTP/1.0 400 Bad Request');
            exit();
        }
        
        //Kontrola, zda je nějaký uživatel přihlášen
        $aChecker = new AccessChecker();
        if (!$aChecker->checkUser())
        {
            header('HTTP/1.0 403 Forbidden');
            exit();
        }
        
        header('Content-Type: application/json');
        try
        {
            switch ($_POST['action'])
            {
                case 'request name change':
                    $newName = urldecode($_POST['name']);
                    $user = UserManager::getUser();
                    $user->requestNameChange($newName);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Žádost o změnu jména byla odeslána. Sledujte prosím svou e-mailovou schránku (pokud jste si zde nastavili e-mailovou adresu). V okamžiku, kdy vaši změnu posoudí správce, dostanete zprávu.', array('origin' => $_POST['action']));
                    echo $response->getResponseString();
                    break;
                case 'change password':
                    $oldPassword = urldecode($_POST['oldPassword']);
                    $newPassword = urldecode($_POST['newPassword']);
                    $rePassword = urldecode($_POST['rePassword']);
                    $user = UserManager::getUser();
                    $user->changePassword($oldPassword, $newPassword, $rePassword);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Heslo bylo úspěšně změněno', array('origin' => $_POST['action']));
                    echo $response->getResponseString();
                    break;
                case 'change email':
                    $password = urldecode($_POST['password']);
                    $email = urldecode($_POST['newEmail']);
                    $user = UserManager::getUser();
                    $user->changeEmail($password, $email);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'E-mail byl úspěšně změněn', array('origin' => $_POST['action']));
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
                    exit();
                    break;
                case 'verify password':
                    $password = urldecode($_POST['password']);
                    if (mb_strlen($password) === 0)
                    {
                        throw new AccessDeniedException(AccessDeniedException::REASON_NO_PASSWORD_GENERAL);
                    }
                    if (!$aChecker->recheckPassword($password))
                    {
                        throw new AccessDeniedException(AccessDeniedException::REASON_WRONG_PASSWORD_GENERAL);
                    }
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, '', array('verified' => true));
                    echo $response->getResponseString();
                    break;
                default:
                    header('HTTP/1.0 400 Bad Request');
                    exit();
            }
        }
        catch (AccessDeniedException $e)
        {
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage(), array('origin' => $_POST['action']));
            echo $response->getResponseString();
        }
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}

