<?php
/** 
 * Třída reprezentujcí žádost o změnu jména uživatele nebo třídy
 * @author Jan Štěch
 */
class NameChangeRequest
{
    const TYPE_CLASS = 'class';
    const TYPE_USER = 'user';
    
    private $id;
    private $type;
    private $object;
    private $newName;
    private $requestedAt;
    
    /**
     * Konstruktor žádosti o změnu jména
     * @param int $id ID žádosti v databázi
     * @param string $type Typ objektu žádosti (uživatel / třída); musí být hodnota jedné z konstant této třídy
     * @param object $object Instance třídy ClassObject, pokud žádost požaduje změnu jména třídy, nebo instance třídy User, pokud žádost požaduje změnu jména uživatele; nepovinné, v případě nevyplnění jakéhokoliv nepovinného argumentu budou všechna data načtena z databáze podle ID a typu
     * @param string $newName Požadované nové jméno třídy nebo uživatele; nepovinné, v případě nevyplnění jakéhokoliv nepovinného argumentu budou všechna data načtena z databáze podle ID a typu
     * @param DateTime $requestedTime Čas, ve kterém byla žádost podána; nepovinné, v případě nevyplnění jakéhokoliv nepovinného argumentu budou všechna data načtena z databáze podle ID a typu
     * @throws BadMethodCallException V případě, že první argument není instance ClassObject nebo User
     */
    public function __construct(int $id, string $type, object $object = null, string $newName = "", DateTime $requestedTime = null)
    {
        $this->id = $id;
        $this->type = $type;
        
        if ((!empty($id) && !empty($type)) && (empty($object) || empty($newName) || empty($requestedTime)))
        {
            //Je zadáno pouze ID a typ - načti ostatní data z databáze
            $this->loadData();
            return;
        }
        
        if (($type === self::TYPE_CLASS && $object instanceof ClassObject) || ($type === self::TYPE_USER && $object instanceof User))
        {
            $this->object = $object;
            $this->type = $type;
        }
        else
        {
            throw new BadMethodCallException('Second argument must be a value of this class\' constants and third argument must be instance of User or ClassObject');
        }
        
        $this->newName = $newName;
        $this->requestedAt = $requestedTime;
    }
    
    /**
     * Metoda načítající všechna potřebná data z databáze podle již uloženého ID žádosti a typu objektu žádosti a ukládající je jako vlastnosti objektu
     */
    private function loadData()
    {
        Db::connect();
        
        if ($this->type === self::TYPE_USER)
        {
            $result = Db::fetchQuery('
            SELECT
            uzivatele.uzivatele_id, uzivatele.jmeno, uzivatele.email, uzivatele.posledni_prihlaseni, uzivatele.pridane_obrazky, uzivatele.uhodnute_obrazky, uzivatele.karma, uzivatele.status,
            zadosti_jmena_uzivatele.nove, zadosti_jmena_uzivatele.cas
            FROM zadosti_jmena_uzivatele
            JOIN uzivatele ON zadosti_jmena_uzivatele.uzivatele_id = uzivatele.uzivatele_id
            WHERE zadosti_jmena_uzivatele.zadosti_jmena_uzivatele_id = ? LIMIT 1;
            ', array($this->id), false);
            
            $user = new User($result['uzivatele_id'], $result['jmeno'], $result['email'], new DateTime($result['posledni_prihlaseni']), $result['pridane_obrazky'], $result['uhodnute_obrazky'], $result['karma'], $result['status']);
            $this->object = $user;
            $this->newName = $result['nove'];
            $this->requestedAt = $result['cas'];
        }
        else
        {
            $result = Db::fetchQuery('
            SELECT
            uzivatele.uzivatele_id, uzivatele.jmeno, uzivatele.email, uzivatele.posledni_prihlaseni, uzivatele.pridane_obrazky, uzivatele.uhodnute_obrazky, uzivatele.karma, uzivatele.status AS "u_status",
            tridy.tridy_id, tridy.nazev, tridy.status AS "c_status", tridy.poznavacky, tridy.kod,
            zadosti_jmena_tridy.nove, zadosti_jmena_tridy.cas
            FROM zadosti_jmena_tridy
            JOIN tridy ON zadosti_jmena_tridy.tridy_id = tridy.tridy_id
            JOIN uzivatele ON tridy.spravce = uzivatele.uzivatele_id
            WHERE zadosti_jmena_tridy.zadosti_jmena_tridy_id = ? LIMIT 1;
            ', array($this->id), false);
            $admin = new User($result['uzivatele_id'], $result['jmeno'], $result['email'], new DateTime($result['posledni_prihlaseni']), $result['pridane_obrazky'], $result['uhodnute_obrazky'], $result['karma'], $result['u_status']);
            $class = new ClassObject($result['tridy_id'], $result['nazev'], $result['c_status'], $result['kod'], $result['poznavacky'], $admin);
            
            $this->object = $class;
            $this->newName = $result['nove'];
            $this->requestedAt = $result['cas'];
        }
    }
    
    /**
     * Metoda navracející ID této žádosti
     * @return int ID žádosti v databázi
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Metoda navracející aktuální jméno třídy nebo uživatele
     * @return string Stávající jméno
     */
    public function getOldName()
    {
        if ($this->type === self::TYPE_CLASS)
        {
            //Jméno třídy
            return $this->object->getName();
        }
        //Jméno uživatele
        return $this->object['name'];
    }
    
    /**
     * Metoda navracejícící požadované jméno třídy nebo uživatele
     * @return string Požadované nové jméno
     */
    public function getNewName()
    {
        return $this->newName;
    }
    
    /**
     * Metoda navracející e-mail žadatale o změnu jména (uživatele nebo správce třídy)
     * @return User
     */
    public function getRequestersEmail()
    {
        if ($this->type === self::TYPE_CLASS)
        {
            //Změna jména třídy
            return $this->object->getAdmin()['email'];
        }
        //Změna jména uživatele
        return $this->object['email'];
    }
    
    /**
     * Metoda schvalující tuto žádost
     * Jméno uživatele nebo třídy je změněno a žadatel obdrží e-mail (pokud jej zadal)
     */
    public function approve()
    {
        //TODO
    }
    
    /**
     * Metoda zamítající tuto žádost
     * Pokud žadatel zadal svůj e-mail, obdrží zprávu s důvodem zamítnutí
     * @param string $reason Důvod zamítnutí žádosti
     */
    public function decline(string $reason)
    {
        //TODO
    }
    
    /**
     * Metoda odstraňující záznam o této žádosti z databáze
     * Data z vlastností této instance jsou vynulována
     * Instance, na které je tato metoda provedena by měla být ihned zničena pomocí unset()
     */
    public function erase()
    {
        //TODO
    }
}