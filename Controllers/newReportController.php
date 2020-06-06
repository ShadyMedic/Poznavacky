<?php
/** 
 * Kontroler volaný pomocí AJAX, který zajišťuje uložení nového hlášení do databáze
 * @author Jan Štěch
 */
class newReportController extends Controller
{
    /**
     * Metoda přijímající URL nahlašovaného obrázku, důvod a přídavné informace skrz $_POST a po ověření ukládající data do databáze
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        $adder = new ReportAdder($_SESSION['selection']['group']);
        try
        {
            $adder->processFormData($_POST);
            echo json_encode(array('msg' => 'Obrázek byl nahlášen. Správce bude moci hlášení posoudit a vyřešit.'));
        }
        catch (AccessDeniedException $e)
        {
            echo json_encode(array('msg' => $e->getMessage()));
        }
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}