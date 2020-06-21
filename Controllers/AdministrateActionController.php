<?php
/**
 * Kontroler zpracovávající data odeslaná ze stránky administrate AJAX požadavkem
 * @author Jan Štěch
 */
class AdministrateActionController extends Controller
{
    /**
     * Metoda odlišující, jaká akce má být vykonána a volající příslušný model
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
        //Kontrola, zda je přihlášený uživatel administrátorem
        if (!AccessChecker::checkSystemAdmin())
        {
            header('HTTP/1.0 403 Forbidden');
            exit();
        }
        
        $administration = new Administration();
        try
        {
            switch ($_POST['action'])
            {
                case 'delete user':
                    $userId = $_POST['userId'];
                    $user = new User($userId, 'null');  //Jméno (druhý argument) je sice povinné, ale vzhledem k tomu, že nebude potřeba a že tento objekt uživatele bude prakticky ihned zničen, můžeme využít tento malý hack
                    $user->deleteAccountAsAdmin();
                    unset($user);
                    echo json_encode(array('messageType' => 'success', 'message' => 'Uživatel úspěšně odstraněn'));
                    break;
                case 'accept user name change':
                case 'accept class name change':
                case 'decline user name change':
                case 'decline class name change':
                    $requestId = $_POST['reqId'];
                    $classNameChange = (mb_stripos($_POST['action'], 'user') !== false) ? false : true;
                    $approved = (mb_stripos($_POST['action'], 'decline') !== false) ? false : true;
                    if (!$approved){ $reason = $_POST['reason']; }
                    else { $reason = ""; }
                    $administration->resolveNameChange($requestId, $classNameChange, $approved, $reason);
                    echo json_encode(array('messageType' => 'success', 'message' => 'Změna jména úspěšně schválena nebo zamítnuta'));
                    break;
                case 'preview email':
                    $msg = $_POST['htmlMessage'];
                    $footer = $_POST['htmlFooter'];
                    $result = $administration->previewEmail($msg, $footer);
                    echo json_encode(array('content' => $result));
                    break;
                case 'send email':
                    $to = $_POST['addressee'];
                    $subject = $_POST['subject'];
                    $msg = $_POST['htmlMessage'];
                    $footer = $_POST['htmlFooter'];
                    $fromAddress = $_POST['fromAddress'];
                    $sender = $_POST['sender'];
                    $administration->sendEmail($to, $subject, $msg, $footer, $sender, $fromAddress);
                    echo json_encode(array('messageType' => 'success', 'message' => 'E-mail byl úspěšně odeslán'));
                    break;
                case 'execute sql query':
                    $query = $_POST['query'];
                    $result = $administration->executeSqlQueries($query);
                    echo json_encode(array('dbResult' => $result));
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