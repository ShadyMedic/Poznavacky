<?php
/**
 * Kontroler zpracovávající data odeslaná ze stránky manage
 * @author Jan Štěch
 */
class ClassUpdateController extends Controller
{
    /**
     * Metoda odlišující, jakou akci si přeje správce třídy provést a volající příslušný model
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        if (empty($_POST))
        {
            header('HTTP/1.0 400 Bad Request');
            exit();
        }
        
        //Kontrola, zda je zvolena nějaká třída
        if (!isset($_SESSION['selection']['class']))
        {
            echo json_encode(array('messageType' => 'error', 'message' => AccessDeniedException::REASON_CLASS_NOT_CHOSEN, 'origin' => $_POST['action']));
            exit();
        }
        $class = $_SESSION['selection']['class'];
        
        //Kontrola, zda je nějaký uživatel přihlášen a zda je přihlášený uživatel správcem vybrané třídy
        if (!AccessChecker::checkUser() || !$class->checkAdmin(UserManager::getId()))
        {
            header('HTTP/1.0 403 Forbidden');
            exit();
        }
        
        try
        {
            switch ($_POST['action'])
            {
                case 'request name change':
                    //TODO
                    break;
                case 'update access':
                    //TODO
                    break;
                case 'kick member':
                    //TODO
                    break;
                case 'invite user':
                    //TODO
                    break;
                case 'create test':
                    //TODO
                    break;
                case 'delete test':
                    //TODO
                    break;
                case 'delete class':
                    //TODO
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