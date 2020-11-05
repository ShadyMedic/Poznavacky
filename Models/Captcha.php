<?php
/**
 * Třída, ze které dědí třídy generující Turingův test pro odlišení lidí od robotů
 * @author Jan Štěch
 */
abstract class Captcha
{
    public $question;
    protected $answer;
    
    /**
     * Metoda generující otázku i odpověď Turingova testu a nastavuje je jako vlastnosti objektu
     */
    public abstract function generate(): void;
    
    /**
     * Metoda ukládající dříve vygenerovanou odpověď uloženou jako vlastnost objektu do $_SESSION
     * @param string $index Klíč, pod kterým bude odpoveď v $_SESSION dostupná
     */
    protected function setAnswer(string $index): void
    {
        $_SESSION[$index] = $this->answer;
    }
    
    /**
     * Metoda odstraňující správnou odpoveď ze $_SESSION
     * @param string $index Klíč, pod kterým se v $_SESSION nachází správná odpoveď
     */
    protected function unsetAnswer($index): void
    {
        unset($_SESSION[$index]);
    }
    
    /**
     * Metoda kontrolující, zda je odpověď zadaná uživatelem shodná s dříve uloženou správnou odpovědí
     * Po zkontrolování je správná odpoveď ze sezení vymazána
     * @param mixed $answer Odpověď zadaná uživatelem
     * @param string $index Klíč, pod kterým se v $_SESSION nachází správná odpoveď
     * @return boolean TRUE, pokud se odpoveď shoduje s dříve uloženou správnou odpovědí (i typem), FALSE, pokud ne
     */
    public function checkAnswer($answer, $index): bool
    {
        $result = $_SESSION[$index] === (int)$answer;
        
        $this->unsetAnswer($index);
        
        if ($result)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}