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
        if (!AccessChecker::checkSystemAdmin(UserManager::getId()))
        {
            header('HTTP/1.0 403 Forbidden');
            exit();
        }
        
        $administration = new Administration();
        try
        {
            switch ($_POST['action'])
            {
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
                    echo json_encode(array('message' => 'E-mail byl úspěšně odeslán'));
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