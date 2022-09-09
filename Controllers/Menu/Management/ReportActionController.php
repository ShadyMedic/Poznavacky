<?php
namespace Poznavacky\Controllers\Menu\Management;

use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\Logger;
use Poznavacky\Models\ReportResolver;

/**
 * Kontroler zpracovávající data odeslaná ze stránky reports (správa hlášení v jedné poznávačce)
 * @author Jan Štěch
 */
class ReportActionController extends AjaxController
{
    /**
     * Metoda odlišující, jakou akci si přeje správce třídy provést a volající příslušný model
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @throws DatabaseException
     * @see AjaxController::process()
     */
    public function process(array $parameters): void
    {
        if (!isset($_POST['action'])) {
            (new Logger())->warning('Uživatel s ID {userId} odeslal z IP adresy {ip} požadavek na vyřešení hlášení, avšak nespecifikoval žádnou akci',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            header('HTTP/1.0 400 Bad Request');
            return;
        }
        
        header('Content-Type: application/json');
        //Kontrola, zda je zvolena nějaká třída
        $aChecker = new AccessChecker();
        if (!(isset($_SESSION['selection']['class']) || $aChecker->checkSystemAdmin())) {
            (new Logger())->warning('Uživatel s ID {userId} se pokusil odeslat požadavek na vyřešení hlášení z IP adresy {ip}, avšak neměl zvolenou žádnou třídu a nejednalo se o systémového administrátora',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR,
                AccessDeniedException::REASON_CLASS_NOT_CHOSEN, array('origin' => $_POST['action']));
            echo $response->getResponseString();
            return;
        }
        
        try {
            $resolver = new ReportResolver();
            
            switch ($_POST['action']) {
                case 'update picture':
                    $pictureId = $_POST['pictureId'];
                    $newNatural = trim($_POST['natural']); //Ořež mezery
                    $newUrl = trim($_POST['url']); //Ořež mezery
                    $resolver->editPicture($pictureId, $newNatural, $newUrl);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Údaje obrázku úspěšně upraveny');
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
                    return;
            }
        } catch (AccessDeniedException $e) {
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage(),
                array('origin' => $_POST['action']));
            echo $response->getResponseString();
        }
    }
}

