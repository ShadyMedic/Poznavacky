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
        if ($checker->verify($answer, $questionNum))
        {
            UserManager::getUser()->incrementGuessedPictures();
            echo json_encode(array('result' => 'correct', 'answer' => $checker->lastSavedAnswer));
        }
        else
        {
            echo json_encode(array('result' => 'wrong', 'answer' => $checker->lastSavedAnswer));
        }
        
        //Zastav zpracování skriptu, aby se nevypsaly pohledy
        exit();
    }
}

