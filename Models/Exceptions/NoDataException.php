<?php
namespace Poznavacky\Models\Exceptions;

use \Exception;

/**
 * Výjimka sloužící pro případ, že z databáze nejsou navrácena žádná data, která pohled vyžaduje
 * @author Jan Štěch
 */
class NoDataException extends Exception
{
    const NO_CLASSES = 'Zatím nemáš přístup do žádných tříd';
    const NO_GROUPS = 'V této třídě nejsou žádné poznávačky';
    const NO_PARTS = 'V této poznávačce nejsou žádné části';
    const UNKNOWN_CLASS = 'Třída nenalezena';
    const UNKNOWN_GROUP = 'Poznávačka nenalezena';
    const UNKNOWN_PART = 'Část nenalezena';
    const UNKNOWN_NATURAL = 'Přírodnina nenalezena';
    const UNKNOWN_PICTURE = 'Obrázek nenalezen';
    const UNKNOWN_REPORT = 'Hlášení nenalezeno';
    const UNKNOWN_USER = 'Uživatel nenalezen';
    const UNKNOWN_INVITATION = 'Pozvánka nenalezena';
    const UNKNOWN_NAME_CHANGE_REQUEST = 'Žádost o změnu jména nenalezena';
    const NATURAL_UNASSIGNED = 'Tato přířodnina není přiřazena do žádné poznávačky nebo neexistuje';
    
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

