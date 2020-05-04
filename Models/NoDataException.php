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
    
    /**
     * Konstruktor podmínky
     * @param string $message Obecná zpráva, která může být zobrazena běžnému uživateli
     * @param int $code Číslo chyby, které může být zobrazeno běžnému uživateli
     * @param Exception $previous Předcházející podmínka (pro účely propagace podmínek)
     */
    public function __construct(string $message = null, int $code = null, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

