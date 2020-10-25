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
            $resolver = new ReportResolver();
            
            switch ($_POST['action'])
            {
                case 'update picture':
                    $pictureId = $_POST['pictureId'];
                    $newNatural = $_POST['natural'];
                    $newUrl = $_POST['url'];
                    $resolver->editPicture($pictureId, $newNatural, $newUrl);
                    echo json_encode(array('messageType' => 'success', 'message' => 'Údaje obrázku úspěšně upraveny'));
                    break;
                case 'disable picture':
                    $pictureId = $_POST['pictureId'];
                    $resolver->disablePicture($pictureId);
                    echo json_encode(array('messageType' => 'success', 'message' => 'Obrázek úspěšně skryt'));
                    break;
                case 'delete picture':
                    $pictureId = $_POST['pictureId'];
                    $resolver->deletePicture($pictureId);
                    echo json_encode(array('messageType' => 'success', 'message' => 'Obrázek úspěšně odstraněn'));
                    break;
                case 'delete report':
                    $reportId = $_POST['reportId'];
                    $resolver->deleteReport($reportId);
                    echo json_encode(array('messageType' => 'success', 'message' => 'Hlášení úspěšně odstraněno'));
                    break;
                default:
                    header('HTTP/1.0 400 Bad Request');
                    exit();
            }
        }
        catch (AccessDeniedException | NoDataException $e)
        {
            echo json_encode(array('messageType' => 'error', 'message' => $e->getMessage(), 'origin' => $_POST['action']));
        }
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}