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
            header('HTTP/1.0 417 Expectation Failed');
            exit();
        }
        
        try
        {
            switch ($_POST['action'])
            {
                case 'request name change':
                    $newName = @$_POST['name'];
                    //TODO
                    break;
                case 'change password':
                    $oldPassword = @$_POST['oldPassword'];
                    $newPassword = @$_POST['newPassword'];
                    $rePassword = @$_POST['rePassword'];
                    //TODO
                    break;
                case 'change email':
                    $password = @$_POST['password'];
                    $email = @$_POST['newEmail'];
                    //TODO
                    break;
                case 'delete account':
                    $password = $_POST['password'];
                    //TODO
                    break;
                case 'verify password':
                    $password = $_POST['password'];
                    //TODO
                default:
                    header('HTTP/1.0 400 Bad Request');
                    exit();
            }
        }
        catch (AccessDeniedException $e)
        {
            //TODO
        }
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}

