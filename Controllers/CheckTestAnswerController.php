<?php
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
    public function process(array $parameters)
    {
        $questionNum = $_POST['qNum'];
        $answer = $_POST['ans'];
        
        $checker = new AnswerChecker();
        try
        {
            $result = $checker->verify($answer, $questionNum);
        }
        catch(AccessDeniedException $e)
        {
            //Neplatné číslo otázky nebo jiná chyba při ověřování odpovědi
            echo json_encode(array('result' => 'error', 'answer' => $e->getMessage()));
            exit();
        }
        
        if ($result)
        {
            UserManager::getUser()->incrementGuessedPictures();
            echo json_encode(array('result' => 'correct', 'answer' => $checker->lastSavedAnswer));
        }
        else
        {
            echo json_encode(array('result' => 'wrong', 'answer' => $checker->lastSavedAnswer));
        }
        
        //Vymaž využitou odpověď ze $_SESSION['testAnswers']
        //Tak nebude možné odpověď odeslat znovu a farmit tak uhodnuté obrázky
        unset($_SESSION['testAnswers'][$questionNum]);
        
        //Zastav zpracování skriptu, aby se nevypsaly pohledy
        exit();
    }
}

