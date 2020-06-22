<?php
/**
 * Třída reprezentující objekt třídy (jakože té z reálného světa / obsahující poznávačky)
 * @author Jan Štěch
 */
class ClassObject
{
    public const CLASS_STATUS_PUBLIC = "public";
    public const CLASS_STATUS_PRIVATE = "private";
    public const CLASS_STATUS_LOCKED = "locked";
    
    private $id;
    private $name;
    private $status;
    private $code;
    private $groups;
    private $groupsCount;
    private $admin;
    
    private $accessCheckResult;
    
    /**
     * Konstruktor třídy nastavující její ID a jméno.
     * Pokud je vše specifikováno, nebude potřeba provádět další SQL dotazy
     * Pokud je vyplněno jméno i ID, ale chybí nějaký z dalších argumentů, má jméno přednost před ID
     * @param int $id ID třídy (nepovinné, pokud je specifikováno jméno)
     * @param string $name Jméno třídy (nepovinné, pokud je specifikováno ID)
     * @param string $status Status třídy (musí mít hodnotu jako některá z konstant této třídy; nepovinné, v případě nevyplnění bude načteno společně s kódem třídy z databáze až v případě potřeby)
     * @param int|NULL $code Vstupní kód třídy (nepovinné, v případě potřeby bude načteno společně se statusem třídy z databáze až v případě potřeby; pro nespecifikování použijte hodnotu -1)
     * @param int $groupsCount Počet poznávaček, které třída obsahuje (nepovinné, v případě potřeby bude načteno z databáze; pro nepsecifikování použijte hodnotu -1)
     * @param User $admin Objekt uživatele, který je správcem této třídy (nepovinné, v případě potřeby bude načteno z databáze až v případě potřeby)
     * @throws BadMethodCallException
     */
    public function __construct(int $id, string $name = "", string $status = "", $code = -1, int $groupsCount = -1, User $admin = NULL)
    {
        if (mb_strlen($name) !== 0 && !empty($id))
        {
            //Vše je specifikováno --> nastavit
            $this->id = $id;
            $this->name = $name;
        }
        else if (mb_strlen($name) !== 0)
        {
            Db::connect();
            $result = Db::fetchQuery('SELECT tridy_id FROM tridy WHERE nazev = ? LIMIT 1', array($name), false);
            if (!$result)
            {
                //Třída nebyla v databázi nalezena
                throw new AccessDeniedException(AccessDeniedException::REASON_CLASS_NOT_FOUND);
            }
            $id = $result['tridy_id'];
        }
        else if (!empty($id))
        {
            Db::connect();
            $result = Db::fetchQuery('SELECT nazev FROM tridy WHERE tridy_id = ? LIMIT 1', array($id), false);
            if (!$result)
            {
                //Třída nebyla v databázi nalezena
                throw new AccessDeniedException(AccessDeniedException::REASON_CLASS_NOT_FOUND);
            }
            $name = $result['nazev'];
        }
        else
        {
            throw new BadMethodCallException('At least one of the arguments must be specified.', null, null);
        }
        
        $this->id = $id;
        $this->name = $name;
        
        //Nastavení statusu (pokud byl specifikován)
        if (!empty($status))
        {
            $this->status = $status;
        }
        
        //Nastavení kódu (pokud byl specifikován)
        if ($code !== -1)
        {
            $this->code = $code;
        }
        
        //Nastavení správce (pokud byl specifikován)
        if (!empty($admin))
        {
            $this->admin = $admin;
        }
    }
    
    /**
     * Metoda navrecející ID této třídy
     * @return int ID třídy
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Metoda navracející jméno této třídy
     * @return string Jméno třídy
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Metoda navracející počet poznávaček v této třídě
     * @return int Počet poznávaček
     */
    public function getGroupsCount()
    {
        if (!isset($this->groupsCount))
        {
            $this->loadGroupsCount();
        }
        return $this->groupsCount;
    }
    
    /**
     * Metoda načítající počet poznávaček patřících do této třídy a ukládající je jako vlastnost tohoto objektu
     */
    private function loadGroupsCount()
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT poznavacky FROM tridy WHERE tridy_id = ?', array($this->id), false);
        $this->groupsCount = $result['poznavacky'];
    }
    
    /**
     * Metoda navracející pole poznávaček patřících do této třídy jako objekty
     * Pokud zatím nebyly poznávačky načteny, budou načteny z databáze
     * @return array Pole poznávaček patřících do této třídy jako objekty
     */
    public function getGroups()
    {
        if (!isset($this->groups))
        {
            $this->loadGroups();
        }
        return $this->groups;
    }
    
    /**
     * Metoda načítající poznávačky patřící do této třídy a ukládající je jako vlastnosti do pole jako objekty
     * Pokud zatím nebyl načten počet poznávaček v této třídě, je uložen jako počet prvků v $this->groups
     */
    private function loadGroups()
    {
        $this->groups = array();
        
        Db::connect();
        $result = Db::fetchQuery('SELECT poznavacky_id,nazev,casti FROM poznavacky WHERE tridy_id = ?', array($this->id), true);
        if ($result === false || count($result) === 0)
        {
            //Žádné poznávačky nenalezeny
            $this->groups = array();
        }
        else
        {
            foreach ($result as $groupData)
            {
                $this->groups[] = new Group($groupData['poznavacky_id'], $groupData['nazev'], $this, $groupData['casti']);
            }
        }
        
        if (!isset($this->groupsCount)){ $this->groupsCount = count($this->groups); }
    }
    
    /**
     * Metoda kontrolující, zda má určitý uživatel přístup do této třídy
     * @param int $userId ID ověřovaného uživatele
     * @param bool $forceAgain Pokud je tato funkce jednou zavolána, uloží se její výsledek jako vlastnost tohoto objektu třídy a příště se použije namísto dalšího databázového dotazu. Pokud tuto hodnotu nastavíte na TRUE, bude znovu poslán dotaz na databázi. Defaultně FALSE
     * @return boolean TRUE, pokud má uživatel přístup do třídy, FALSE pokud ne
     */
    public function checkAccess(int $userId, bool $forceAgain = false)
    {
        if (isset($this->accessCheckResult) && $forceAgain === false)
        {
            return $this->accessCheckResult;
        }
        
        Db::connect();
        $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM `tridy` WHERE tridy_id = ? AND (status = "public" OR tridy_id IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = ?));', array($this->id, $userId), false);
        $this->accessCheckResult = ($result['cnt'] === 1) ? true : false;
        return ($result['cnt'] === 1) ? true : false;
    }
    
    /**
     * Metoda kontrolující, zda je určitý uživatel správcem této třídy
     * Pokud zatím nebyl načten správce této třídy, bude načten z databáze
     * @param int $userId ID ověřovaného uživatele
     * @return boolean TRUE, pokud je uživatelem správce třídy, FALSE pokud ne
     */
    public function checkAdmin(int $userId)
    {
        if (!isset($this->admin))
        {
            $this->loadAdmin();
        }
        return ($this->admin['id'] === $userId) ? true : false;
    }
    
    /**
     * Metoda kontrolující, zda v této třídě existuje specifikovaná poznávačka
     * @param string $groupName Jméno poznávačky
     * @return boolean TRUE, pokud byla poznávačka nalezene, FALSE, pokud ne
     */
    public function groupExists(string $groupName)
    {
        Db::connect();
        $cnt = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM poznavacky WHERE nazev = ? AND tridy_id = ?', array($groupName, $this->id), false);
        if ($cnt['cnt'] > 0)
        {
            return true;
        }
        return false;
    }
    
    /**
     * Metoda přidávající uživatele do třídy (přidává spojení uživatele a třídy do tabulky "clenstvi")
     * Pokud je tato třída veřejná nebo uzamčená, nic se nestane
     * @param int $userId ID uživatele získávajícího členství
     * @return boolean TRUE, pokud je členství ve třídě úspěšně přidáno, FALSE, pokud ne (například z důvodu zamknutí třídy)
     */
    public function addMember(int $userId)
    {
        //Zkontroluj, zda je třída soukromá
        if (!$this->getStatus() === self::CLASS_STATUS_PRIVATE)
        {
            //Nelze získat členství ve veřejné nebo uzamčené třídě
            return false;
        }
        
        Db::connect();
        
        //Zkontroluj, zda již uživatel není členem této třídy
        //Není třeba - metoda getNewClassesByAccessCode ve třídě ClassManager navrací pouze třídy, ve kterých přihlášený uživatel ještě není členem
      # if ($this->checkAccess($userId))
      # {
      #     //Nelze získat členství ve třídě vícekrát
      #     return false;
      # }
        
        if (Db::executeQuery('INSERT INTO clenstvi(uzivatele_id,tridy_id) VALUES (?,?)', array($userId, $this->id)))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Metoda odstraňující uživatele ze třídy (odstraňuje spojení uživatele a třídy z tabulky "clenstvi")
     * Pokud je třída veřejná, nic se nestane
     * @param int $userId
     * @return boolean TRUE, v případě, že se odstranění uživatele povede, FALSE, pokud ne
     */
    public function removeMember(int $userId)
    {
        if ($this->status == self::CLASS_STATUS_PUBLIC)
        {
            //Nelze odstranit člena z veřejné třídy
            return false;
        }
        
        Db::connect();
        if (Db::executeQuery('DELETE FROM clenstvi WHERE tridy_id = ? AND uzivatele_id = ? LIMIT 1', array($this->id, $userId)))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Metoda navracející uložený status této třídy.
     * Pokud status není uložen, je načten z databáze, uložen a poté navrácen
     * @return string Status třídy (viz konstanty třídy)
     */
    public function getStatus()
    {
        if (!isset($this->status))
        {
            $this->loadStatusAndCode();
        }
        return $this->status;
    }
    
    /**
     * Metoda navracející uložený vstupní kód této třídy
     * @return int Čtyřmístný kód této třídy
     */
    public function getCode()
    {
        if (!isset($this->code))
        {
            $this->loadStatusAndCode();
        }
        return $this->code;
    }
    
    /**
     * Metoda získávající z databáze status této třídy a nastavující jej jako vlastnost "status"
     */
    private function loadStatusAndCode()
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT status,kod FROM tridy WHERE tridy_id = ? LIMIT 1', array($this->id), false);
        $this->status = $result['status'];
        $this->code = $result['kod'];
    }
    
    /**
     * Metoda pro získání objektu uživatele, který je správcem této třídy
     * @return User Objekt správce třídy
     */
    public function getAdmin()
    {
        if (!isset($this->admin))
        {
            $this->loadAdmin();
        }
        return $this->admin;
    }
    
    /**
     * Metoda načítající data o uživateli, který je správcem této třídy z databáze a nastavující je jako vlastnost "admin"
     */
    private function loadAdmin()
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT uzivatele.uzivatele_id, uzivatele.jmeno, uzivatele.email, uzivatele.posledni_prihlaseni, uzivatele.pridane_obrazky, uzivatele.uhodnute_obrazky, uzivatele.karma, uzivatele.status FROM tridy JOIN uzivatele ON tridy.spravce = uzivatele.uzivatele_id WHERE tridy_id = ?;', array($this->id), false);
        $this->admin = new User($result['uzivatele_id'], $result['jmeno'], $result['email'], new DateTime($result['posledni_prihlaseni']), $result['pridane_obrazky'], $result['uhodnute_obrazky'], $result['karma'], $result['status']);
    }
    
    /**
     * Metoda upravující přístupová data této třídy z rozhodnutí administrátora
     * @param string $status Nový status třídy (musí být jedna z konstant této třídy)
     * @param int|NULL $code Nový přístupový kód třídy (nepovinné, pokud je status nastaven na "public" nebo "locked")
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem nebo jsou zadaná data neplatná
     * @return boolean TRUE, pokud jsou přístupová data třídy úspěšně aktualizována
     */
    public function updateAccessData(string $status, $code)
    {
        //Nastavení kódu na NULL, pokud je třída nastavená na status, ve kterém by neměl smysl
        if ($status === self::CLASS_STATUS_PUBLIC || $status === self::CLASS_STATUS_LOCKED)
        {
            $code = null;
        }
        
        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        if (!AccessChecker::checkSystemAdmin())
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
        
        //Kontrola platnosti dat
        if (($code !== null && $code < 0 || $code > 9999) || !($status === self::CLASS_STATUS_PUBLIC || $status === self::CLASS_STATUS_PRIVATE || $status === self::CLASS_STATUS_LOCKED))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ADMINISTRATION_CLASS_UPDATE_INVALID_DATA);
        }
        
        //Kontrola dat OK
        
        Db::connect();
        Db::executeQuery('UPDATE tridy SET status = ?, kod = ? WHERE tridy_id = ?;', array($status, $code, $this->id), false);
        
        return true;
    }
    
    /**
     * Metoda odstraňující tuto třídu z databáze na základě rozhodnutí administrátora
     * Data z vlastností této instance jsou vynulována
     * Instance, na které je tato metoda provedena by měla být ihned zničena pomocí unset()
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem
     * @return boolean TRUE, pokud je třída úspěšně odstraněna z databáze
     */
    public function deleteAsAdmin()
    {
        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        if (!AccessChecker::checkSystemAdmin())
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
        
        //Kontrola dat OK
        
        //Odstranit třídu
        Db::connect();
        Db::executeQuery('DELETE FROM tridy WHERE tridy_id = ? LIMIT 1', array($this->id));
        
        //Vymazat data z této instance třídy
        $this->id = null;
        $this->name = null;
        $this->email = null;
        $this->status = null;
        $this->code = null;
        $this->groups = null;
        $this->groupsCount = null;
        $this->admin = null;
        $this->accessCheckResult = null;
        
        return true;
    }
}

