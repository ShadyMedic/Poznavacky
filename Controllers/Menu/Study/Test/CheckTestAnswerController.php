<?php
namespace Poznavacky\Controllers\Menu\Study\Test;

/** 
 * Kontroler volaný pomocí AJAX, který ověřuje odpověď zadanou uživatelem na testovací stránce
 * @author Jan Štěch
 */
class CheckTestAnswerController extends Controller
{
    /**
     * Metoda načítající odpověď z $_POST a ověřuje jí proti správné odpovědi uložené v $_SESSION
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        $questionNum = $_POST['qNum'];
        $answer = $_POST['ans'];
        
        header('Content-Type: application/json');
        $checker = new AnswerChecker();
        try
        {
            $result = $checker->verify($answer, $questionNum);
        }
        catch(AccessDeniedException $e)
        {
            //Neplatné číslo otázky nebo jiná chyba při ověřování odpovědi
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage());
            echo $response->getResponseString();
            exit();
        }
        
        if ($result)
        {
            UserManager::getUser()->incrementGuessedPictures();
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_INFO, 'correct', array('answer' => $checker->lastSavedAnswer));
            echo $response->getResponseString();
        }
        else
        {
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_INFO, 'wrong', array('answer' => $checker->lastSavedAnswer));
            echo $response->getResponseString();
        }
        
        //Vymaž využitou odpověď ze $_SESSION['testAnswers']
        //Tak nebude možné odpověď odeslat znovu a farmit tak uhodnuté obrázky
        unset($_SESSION['testAnswers'][$questionNum]);
        
        //Zastav zpracování skriptu, aby se nevypsaly pohledy
        exit();
    }
}

