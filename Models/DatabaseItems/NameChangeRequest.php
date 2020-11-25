<?php
namespace Poznavacky\Models\DatabaseItems;

/** 
 * Abstrasktní třída definující společné metody a vlastnosti pro žádost o změnu jména uživatele a žádost o změnu názvu třídy
 * @author Jan Štěch
 */
abstract class NameChangeRequest extends DatabaseItem
{
    protected const DEFAULT_VALUES = array(
        /*Všechny vlastnosti musí být vyplněné před uložením do databáze*/
    );
    
    protected $subject;
    protected $newName;
    protected $requestedAt;
    
    /**
     * Konstruktor žádosti o změnu jména nastavující její ID nebo informaci o tom, že je nová
     * @param bool $isNew FALSE, pokud je již žádost se zadaným ID nebo později doplněnými informacemi uložena v databázi, TRUE, pokud se jedná o novou žádost
     * @param int $id ID žádosti (možné pouze pokud je první argument FALSE; pokud není vyplněno, bude načteno z databáze po vyplnění dalších údajů o ní pomocí metody NameChangeRequest::initialize())
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function __construct(bool $isNew, int $id = 0)
    {
        parent::__construct($isNew, $id);
    }
    
    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * Při nastavení některého z argumentů na undefined, je hodnota dané vlastnosti také nastavena na undefined
     * Při nastavení některého z argumentů na null, není hodnota dané vlastnosti nijak pozměněna
     * @param object|undefined|null $subject Instance třídy ClassObject, pokud žádost požaduje změnu jména třídy, nebo instance třídy User, pokud žádost požaduje změnu jména uživatele
     * @param string|undefined|null $newName Požadované nové jméno třídy nebo uživatele
     * @param DateTime|undefined|null $requestedAt Čas, ve kterém byla žádost podána
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($subject = null, $newName = null, $requestedAt = null): void
    {   
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($subject === null){ $subject = $this->subject; }
        if ($newName === null){ $newName = $this->newName; }
        if ($requestedAt === null){ $requestedAt = $this->requestedAt; }
        
        $this->subject = $subject;
        $this->newName = $newName;
        $this->requestedAt = $requestedAt;
    }
    
    /**
     * Metoda navracejícící požadované jméno
     * @return string Požadované nové jméno
     */
    public function getNewName(): string
    {
        $this->loadIfNotLoaded($this->newName);
        return $this->newName;
    }
    
    /**
     * Metoda navracející aktuální jméno uživatele nebo název třídy
     * @return string Stávající jméno uživatele nebo název třídy
     */
    public abstract function getOldName(): string;
    
    /**
     * Metoda navracející e-mail uživatele žádající o změnu svého jména nebo názvu třídy (v takovém případě e-mail správce třídy)
     * @return string E-mailová adresa autora této žádosti
     */
    public abstract function getRequestersEmail(): string;
    
    /**
     * Metoda odesílající autorovi této žádosti e-mail o potvrzení změny jména (pokud uživatel zadal svůj e-mail)
     */
    public abstract function sendApprovedEmail(): bool;
    
    /**
     * Metoda odesílající autorovi této žádosti e-mail o jejím zamítnutí (pokud uživatel zadal svůj e-mail)
     * @param string $reason Důvod k zamítnutí jména uživatele nebo názvu třídy zadaný správcem
     */
    public abstract function sendDeclinedEmail(string $reason): bool;
    
    /**
     * Metoda schvalující tuto žádost
     * Jméno uživatele nebo třídy je změněno a žadatel obdrží e-mail (pokud jej zadal)
     * @return TRUE, pokud se vše povedlo, FALSE, pokud se nepodařilo odeslat e-mail
     */
    public function approve(): bool
    {
        $this->loadIfNotLoaded($this->newName);
        $this->loadIfNotLoaded($this->subject);
        
        //Změnit jméno
        Db::executeQuery('UPDATE '.$this::SUBJECT_TABLE_NAME.' SET '.$this::SUBJECT_NAME_DB_NAME.' = ? WHERE '.$this::SUBJECT_TABLE_NAME.'_id = ?;', array($this->newName, $this->subject->getId()));
        
        //Odeslat e-mail
        return $this->sendApprovedEmail();
    }
    
    /**
     * Metoda zamítající tuto žádost
     * Pokud žadatel zadal svůj e-mail, obdrží zprávu s důvodem zamítnutí
     * @param string $reason Důvod zamítnutí žádosti
     * @return TRUE, pokud se vše povedlo, FALSE, pokud se nepodařilo odeslat e-mail
     */
    public function decline(string $reason): bool
    {
        $this->loadIfNotLoaded($this->subject);
        
        //Odeslat e-mail
        return $this->sendDeclinedEmail($reason);
    }
}

