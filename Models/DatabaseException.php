<?php
/**
 * Výjimka sloužící pro databázové chyby
 * @author Jan Štěch
 */
class DatabaseException extends Exception
{
    private $query;
    private $dbErrorCode;
    private $dbErrorMessage;
    
    /**
     * Konstruktor databázové podmínky
     * @param string $message Obecná zpráva, která může být zobrazena běžnému uživateli
     * @param int $code Číslo chyby, které může být zobrazeno běžnému uživateli
     * @param Exception $previous Předcházející podmínka (pro účely propagace podmínek)
     * @param string $query SQL dotaz, který selhal (nesmí být zobrazován běžnému uživateli)
     * @param int $dbErrorCode Číslo dazabázové chyby (nesmí být zobrazené běžnému uživateli)
     * @param string $dbErrorMessage Text databázové chyby (nesmí být zobrazené běžnému uživateli)
     */
    public function __construct(string $message, $code = -1, $previous = null, string $query, string $dbErrorCode, string $dbErrorMessage)
    {
        parent::__construct($message, $code, $previous);
        $this->query = $query;
        $this->dbErrorCode = $dbErrorCode;
        $this->dbErrorMessage = $dbErrorMessage;
    }
    
    /**
     * Metoda navracející informace o výjimce, které mohou být zobrazeny běžnému uživateli
     * @param bool $message TRUE, pokud se má navrátit zpráva o výjimce
     * @param bool $code TRUE, pokud se má navrátit chybový kód
     * @return string Řetězec ve formátu "<zpráva> - error code: <kód>" nebo "Error code: <kód>" nebo "<zpráva>" nebo prázdný řetězec
     */
    public function getSafeInfo(bool $message, bool $code): string
    {
        $result = '';
        if ($message){$result .= $this->message;}
        if ($message && $code){$result .= ' - Error code: '.$this->code;}
        else if ($code){$result .= 'An error occured. Error code: '.$this->code;}
        return $result;
    }
    
    /**
     * Metoda navracející pole s informacemi o chybě poskytnuté databází.
     * 
     * TYTO INFORMACE NESMÍ BÝT ZOBRAZOVÁNY BĚŽNÉMU UŽIVATELI - METODA SLOUŽÍ POUZE PRO VÝVOJÁŘSKÉ ÚČELY
     * @return array Pole s indexy "query", "code" a "message" obsahující informace o výjimce
     */
    public function getDbInfo(): array
    {
        return array('query' => $this->query, 'code' => $this->dbErrorCode, 'message' => $this->dbErrorMessage);
    }
    
    /**
     * Metoda zajišťující, že při neočekávaném výskytu výjimky nebudou vypsány citlivé informace z $previous DBOexception
     * {@inheritDoc}
     * @see Exception::__toString()
     */
    public function __toString(): string
    {
        return $this->getSafeInfo(true, true);
    }
}