<?php
namespace Poznavacky\Controllers\Menu\Study;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Processors\ReportAdder;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\Logger;

/** 
 * Kontroler volaný pomocí AJAX, který zajišťuje uložení nového hlášení do databáze
 * @author Jan Štěch
 */
class NewReportController extends Controller
{
    /**
     * Metoda přijímající URL nahlašovaného obrázku, důvod a přídavné informace skrz $_POST a po ověření ukládající data do databáze
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        header('Content-Type: application/json');
        $adder = new ReportAdder($_SESSION['selection']['group']);
        try
        {
            $adder->processFormData($_POST);
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Obrázek byl nahlášen. Správce bude moci hlášení posoudit a vyřešit.');
            echo $response->getResponseString();
        }
        catch (AccessDeniedException $e)
        {
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage());
            echo $response->getResponseString();
        }
        catch (DatabaseException $e)
        {
            (new Logger(true))->alert('Uživatel s ID {userId} se pokusil nahlásit obrázek s URL {picUrl} v poznávačce s ID {groupUrl}, avšak při práci s databází se vyskytla chyba; pokud toto není ojedinělá chyba, je možné, že tato část systému nefunguje nikomu; chybová hláška: {exception}', array('userId' => UserManager::getId(), 'picUrl' => $_POST['picUrl'], 'groupId' => $_SESSION['selection']['group']->getId(), 'exception' => $e));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, AccessDeniedException::REASON_UNEXPECTED);
            echo $response->getResponseString();
        }
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}

