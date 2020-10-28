<?php
/** 
 * Třída ověřující odpověď zadanou uživatelem na testovací stránce
 * @author Jan Štěch
 */
class AnswerChecker
{
    private const TOLERANCE = 0.34;    //Maximální povolený poměr (špatné znaky / všechny znaky), aby byla odpověď uznána
    
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
            throw new AccessDeniedException(AccessDeniedException::REASON_TEST_ANSWER_CHECK_INVALID_QUESTION, null, null);
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
        //Převést vše na malá písmena
        $answer = mb_strtolower($answer);
        $correct = mb_strtolower($correct);
        
        //Odstranit diakritiku
        //Kód napsaný podle odpovědi na StackOverflow: https://stackoverflow.com/a/35178027
        $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;', Transliterator::FORWARD);
        $answer = $transliterator->transliterate($answer);
        $correct = $transliterator->transliterate($correct);
        
        if ($answer === $correct)
        {
            //Odpověď bez překlepů
            return true;
        }
        
        //Dorovnání délky odpovědi
        while (mb_strlen($correct) > mb_strlen($answer)){ $answer .= 'Ø'; }
        
        //Připojení dvou dalších znaků na konec obou řetězců, aby cyklus níže nevyvolával OutOfRangeException
        $answer .= '¶¶';
        $correct .= '¶¶';
        
        $errors = 0;
        
        for ($i = 0; $i < mb_strlen($correct) - 2; $i++)
        {
            if ($answer[$i] !== $correct[$i])    //Neshodný znak
            {
                if ($answer[$i] == $correct[$i+1] && $answer[$i+1] == $correct[$i+2])    //Chybějící znak - další dva znaky jsou posunuté
                {
                    $answer = substr($answer, 0, $i).$correct[$i].substr($answer,$i);   //Přidávání chybějícího znaku
                    $errors++;
                }
                
                else if ($answer[$i+1] == $correct[$i] && $answer[$i+2] == $correct[$i+1])    //Přebývající znak - další dva znaky jsou posunuté
                {
                    $answer = substr($answer, 0, $i).substr($answer, $i+1);    //Odstraňování přebývajícího znaku
                    $errors++;
                }
                
                else    //Špatný znak
                {
                    $answer = substr($answer, 0, $i).$correct[$i].substr($answer, $i+1);    //Oprava špatného znaku
                    $errors++;
                }
            }
        }
        
        //Výpočet poměru chyb k počtu znaků (- 2, aby nebyly počítány uměle přidané znaky na konec - viz výše)
        if (($errors / (mb_strlen($correct) - 2)) > self::TOLERANCE)
        {
            //Vyší poměr, než je dovoleno
            return false;
        }
        //Nižší poměr, než je dovoleno
        return true;
    }
}