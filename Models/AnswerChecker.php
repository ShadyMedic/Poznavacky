<?php
/** 
 * Třída ověřující odpověď zadanou uživatelem na testovací stránce
 * @author Jan Štěch
 */
class AnswerChecker
{
    public $lastResult;
    public $lastSavedAnswer;
    
    /**
     * Metoda pro ověření správnosti odpovědi
     * @param string $answer Odpověď zadaná uživatelem
     * @param int $questionNum Číslo, pod kterým je v $_SESSION['testAnswers'] uložena správná odpověď
     * @return bool TRUE, pokud je odpověď správná, FALSE, pokud ne
     */
    public function verify(string $answer, int $questionNum)
    {
        if (!isset($_SESSION['testAnswers'][$questionNum]))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_TEST_ANSWER_CHECK_INVALID_QUESTION, null, null, array('originalFile' => 'AnswerChecker.php', 'displayOnView' => 'test.phtml'));
        }
        $correct = $_SESSION['testAnswers'][$questionNum];
        $this->lastSavedAnswer = $correct;
        
        if ($this->isCorrect($answer, $correct))
        {
            return true;
        }
        return false;
    }
    
    /**
     * Metoda ověřující, zda lze uznanou odpověď uznat jako správnou (s tolerancí překlepů)
     * @param string $answer
     * @param string $correct
     * @return bool TRUE, pokud lze odpověď uznat, FALSE, pokud ne
     */
    private function isCorrect(string $answer, string $correct)
    {
        //TODO ověřit odpověď
        return true;
    }
}

