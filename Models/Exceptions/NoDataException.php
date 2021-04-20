<?php
namespace Poznavacky\Models\Exceptions;

use \Exception;
use \Throwable;

/**
 * Výjimka sloužící pro případ, že z databáze nejsou navrácena žádná data, která pohled vyžaduje
 * @author Jan Štěch
 */
class NoDataException extends Exception
{
    public const NO_CLASSES = 'Zatím nemáš přístup do žádných tříd';
    public const NO_GROUPS = 'V této třídě nejsou žádné poznávačky';
    public const NO_PARTS = 'V této poznávačce nejsou žádné části';
    public const UNKNOWN_CLASS = 'Třída nenalezena';
    public const UNKNOWN_GROUP = 'Poznávačka nenalezena';
    public const UNKNOWN_PART = 'Část nenalezena';
    public const UNKNOWN_NATURAL = 'Přírodnina nenalezena';
    public const UNKNOWN_PICTURE = 'Obrázek nenalezen';
    public const UNKNOWN_REPORT = 'Hlášení nenalezeno';
    public const UNKNOWN_USER = 'Uživatel nenalezen';
    public const UNKNOWN_INVITATION = 'Pozvánka nenalezena';
    public const UNKNOWN_NAME_CHANGE_REQUEST = 'Žádost o změnu jména nenalezena';
    public const NATURAL_UNASSIGNED = 'Tato přířodnina není přiřazena do žádné poznávačky nebo neexistuje';
    
    /**
     * Konstruktor podmínky
     * @param string|null $message Obecná zpráva, která může být zobrazena běžnému uživateli
     * @param int|null $code Číslo chyby, které může být zobrazeno běžnému uživateli
     * @param Throwable|null $previous Předcházející podmínka (pro účely propagace podmínek)
     */
    public function __construct(string $message = null, int $code = null, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

