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
    public function process(array $parameters)
    {
        if (empty($_POST))
        {
            header('HTTP/1.0 400 Bad Request');
            exit();
        }
        
        //Kontrola, zda je nějaký uživatel přihlášen
        if (!AccessChecker::checkUser())
        {
            header('HTTP/1.0 403 Forbidden');
            exit();
        }
        
        try
        {
            switch ($_POST['action'])
            {
                case 'request name change':
                    $newName = urldecode($_POST['name']);
                    $user = UserManager::getUser();
                    $user->requestNameChange($newName);
                    echo json_encode(array('messageType' => 'success', 'message' => 'Žádost o změnu jména byla odeslána. Sledujte prosím svou e-mailovou schránku (pokud jste si zde nastavili e-mailovou adresu). V okamžiku, kdy vaši změnu posoudí správce, dostanete zprávu.', 'origin' => $_POST['action']));
                    break;
                case 'change password':
                    $oldPassword = urldecode($_POST['oldPassword']);
                    $newPassword = urldecode($_POST['newPassword']);
                    $rePassword = urldecode($_POST['rePassword']);
                    $user = UserManager::getUser();
                    $user->changePassword($oldPassword, $newPassword, $rePassword);
                    echo json_encode(array('messageType' => 'success', 'message' => 'Heslo bylo úspěšně změněno', 'origin' => $_POST['action']));
                    break;
                case 'change email':
                    $password = urldecode($_POST['password']);
                    $email = urldecode($_POST['newEmail']);
                    $user = UserManager::getUser();
                    $user->changeEmail($password, $email);
                    echo json_encode(array('messageType' => 'success', 'message' => 'E-mail byl úspěšně změněn', 'origin' => $_POST['action']));
                    break;
                case 'delete account':
                    $password = urldecode($_POST['password']);
                    $user = UserManager::getUser();
                    $user->deleteAccount($password);
                    //Naposled přesměruj uživatele ven ze systému
                    echo json_encode(array('redirect' => ''));
                    exit();
                    break;
                case 'verify password':
                    $password = urldecode($_POST['password']);
                    if (mb_strlen($password) === 0)
                    {
                        throw new AccessDeniedException(AccessDeniedException::REASON_NO_PASSWORD_GENERAL);
                    }
                    if (!AccessChecker::recheckPassword($password))
                    {
                        throw new AccessDeniedException(AccessDeniedException::REASON_WRONG_PASSWORD_GENERAL);
                    }
                    echo json_encode(array('verified' => true));
                    break;
                default:
                    header('HTTP/1.0 400 Bad Request');
                    exit();
            }
        }
        catch (AccessDeniedException $e)
        {
            echo json_encode(array('messageType' => 'error', 'message' => $e->getMessage(), 'origin' => $_POST['action']));
        }
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}

