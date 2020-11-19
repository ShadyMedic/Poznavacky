<?php
/**
 * Kontroler zpracovávající data odeslaná ze stránky reports (správa hlášení v jedné poznávačce)
 * @author Jan Štěch
 */
class ReportActionController extends Controller
{
    /**
     * Metoda odlišující, jakou akci si přeje správce třídy provést a volající příslušný model
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        if (empty($_POST))
        {
            header('HTTP/1.0 400 Bad Request');
            exit();
        }
        
        header('Content-Type: application/json');
        //Kontrola, zda je zvolena nějaká třída
        if (!isset($_SESSION['selection']['class']))
        {
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, AccessDeniedException::REASON_CLASS_NOT_CHOSEN, array('origin' => $_POST['action']));
            echo $response->getResponseString();
            exit();
        }
        $class = $_SESSION['selection']['class'];
        
        //Kontrola, zda je nějaký uživatel přihlášen a zda je přihlášený uživatel správcem vybrané třídy
        $aChecker = new AccessChecker();
        if (!$aChecker->checkUser() || !$class->checkAdmin(UserManager::getId()))
        {
            header('HTTP/1.0 403 Forbidden');
            exit();
        }
        
        try
        {
            $resolver = new ReportResolver();
            
            switch ($_POST['action'])
            {
                case 'update picture':
                    $pictureId = $_POST['pictureId'];
                    $newNatural = $_POST['natural'];
                    $newUrl = $_POST['url'];
                    $resolver->editPicture($pictureId, $newNatural, $newUrl);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Údaje obrázku úspěšně upraveny');
                    echo $response->getResponseString();
                    break;
                case 'disable picture':
                    $pictureId = $_POST['pictureId'];
                    $resolver->disablePicture($pictureId);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Obrázek úspěšně skryt');
                    echo $response->getResponseString();
                    break;
                case 'delete picture':
                    $pictureId = $_POST['pictureId'];
                    $resolver->deletePicture($pictureId);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Obrázek úspěšně odstraněn');
                    echo $response->getResponseString();
                    break;
                case 'delete report':
                    $reportId = $_POST['reportId'];
                    $resolver->deleteReport($reportId);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Hlášení úspěšně odstraněno');
                    echo $response->getResponseString();
                    break;
                default:
                    header('HTTP/1.0 400 Bad Request');
                    exit();
            }
        }
        catch (AccessDeniedException | NoDataException $e)
        {
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage(), array('origin' => $_POST['action']));
            echo $response->getResponseString();
        }
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}