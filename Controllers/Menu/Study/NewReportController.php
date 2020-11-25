<?php
namespace Poznavacky\Controllers\Menu\Study;

/** 
 * Kontroler volaný pomocí AJAX, který zajišťuje uložení nového hlášení do databáze
 * @author Jan Štěch
 */
class NewReportController extends Controller
{
    /**
     * Metoda přijímající URL nahlašovaného obrázku, důvod a přídavné informace skrz $_POST a po ověření ukládající data do databáze
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
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}

