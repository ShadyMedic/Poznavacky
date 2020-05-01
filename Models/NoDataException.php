<?php
/**
 * Výjimka sloužící pro případ, že z databáze nejsou navrácena žádná data, která pohled vyžaduje
 * @author Jan Štěch
 */
class NoDataException extends Exception
{
    const NO_CLASSES = 'Zatím nemáš přístup do žádných tříd';
    const NO_GROUPS = 'V této třídě nejsou žádné poznávačky';
    const NO_PARTS = 'V této poznávačce nejsou žádné části';
    
    private $tableLevel;
    
    /**
     * Konstruktor podmínky
     * @param string $message Obecná zpráva, která může být zobrazena běžnému uživateli
     * @param int $code Číslo chyby, které může být zobrazeno běžnému uživateli
     * @param Exception $previous Předcházející podmínka (pro účely propagace podmínek)
     * @param int $tableLevel Úroveň prohlížené složky v menu tabulce - 0 = tabulka tříd; 1 = tabulka poznávaček; 2 = tabulka částí
     */
    public function __construct(string $message = null, int $code = null, Exception $previous = null, int $tableLevel)
    {
        parent::__construct($message, $code, $previous);
        $this->tableLevel = $tableLevel;
    }
    
    /**
     * Funkce navracející úroveň prohlížené složky v menu tabulce
     * @return int Celé číslo - 0 = tabulka tříd; 1 = tabulka poznávaček; 2 = tabulka částí
     */
    public function getTableLevel()
    {
        return $this->tableLevel;
    }
}

