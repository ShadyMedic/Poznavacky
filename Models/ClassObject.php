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
    
    public const CLASS_STATUSES_DICTIONARY = array(
        'veřejná' => self::CLASS_STATUS_PUBLIC,
        'soukromá' => self::CLASS_STATUS_PRIVATE,
        'uzamčená' => self::CLASS_STATUS_LOCKED
    );
    
    private $id;
    private $name;
    private $status;
    private $code;
    private $groups;
    private $groupsCount;
    private $members;
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
     * @return Group[] Pole poznávaček patřících do této třídy jako objekty
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
     * Metoda navracející pole členů této třídy jako objekty
     * Pokud zatím nebyly členové načteny, budou načteny z databáze
     * @return User[] Pole členů patřících do této třídy jako instance třídy User
     */
    public function getMembers()
    {
        if (!isset($this->groups))
        {
            $this->loadMembers();
        }
        return $this->members;
    }
    
    /**
     * Metoda načítající členy patřící do této třídy a ukládající je jako vlastnosti do pole jako objekty
     */
    private function loadMembers()
    {
        $this->members = array();
        
        Db::connect();
        $result = Db::fetchQuery('SELECT uzivatele.uzivatele_id,uzivatele.jmeno,uzivatele.email,uzivatele.posledni_prihlaseni,uzivatele.pridane_obrazky,uzivatele.uhodnute_obrazky,uzivatele.karma,uzivatele.status FROM clenstvi JOIN '.User::TABLE_NAME.' ON clenstvi.uzivatele_id = uzivatele.uzivatele_id WHERE clenstvi.tridy_id = ? AND uzivatele.uzivatele_id != ? ORDER BY uzivatele.posledni_prihlaseni DESC;', array($this->id, UserManager::getId()), true);
        if ($result === false || count($result) === 0)
        {
            //Žádné poznávačky nenalezeny
            $this->members = array();
        }
        else
        {
            foreach ($result as $memberData)
            {
                $user = new User(false, $memberData['uzivatele_id']);
                $user->initialize($memberData['jmeno'], $memberData['email'], new DateTime($memberData['posledni_prihlaseni']), $memberData['pridane_obrazky'], $memberData['uhodnute_obrazky'], $memberData['karma'], $memberData['status']);
                $this->members[] = $user;
            }
        }
    }
    
    /**
     * Metoda vytvářející pozvánku do této třídy pro určitého uživatele
     * Pokud byl již uživatel do této třídy pozván, je prodloužena životnost existující pozvánky
     * @param int $userName Jméno uživatele, pro kterého je pozvánka určena
     * @throws AccessDeniedException Pokud je tato třída veřejná, uživatel se zadaným jménem neexistuje nebo je již členem této třídy
     * @return boolean TRUE, pokud je pozvánka úspěšně vytvořena
     */
    public function inviteUser(string $userName)
    {
        //Zkontroluj, zda tato třída není veřejná
        if ($this->status === self::CLASS_STATUS_PUBLIC)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_INVITE_USER_PUBLIC_CLASS);
        }
        
        //Konstrukce objektu uživatele
        Db::connect();
        $result = Db::fetchQuery('SELECT uzivatele_id,jmeno,email,posledni_prihlaseni,pridane_obrazky,uhodnute_obrazky,karma,status FROM '.User::TABLE_NAME.' WHERE jmeno = ? LIMIT 1', array($userName));
        if (empty($result))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_INVITE_USER_UNKNOWN_USER);
        }
        $user = new User(false, $result['uzivatele_id']);
        $user->initialize($result['jmeno'], $result['email'], new DateTime($result['posledni_prihlaseni']), $result['pridane_obrazky'], $result['uhodnute_obrazky'], $result['karma'], $result['status']);
        
        //Zkontroluj, zda uživatel již není členem třídy
        for ($i = 0; $i < count($this->members) && $user['id'] !== $this->members[$i]['id']; $i++){}
        if ($i !== count($this->members))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_INVITE_USER_ALREADY_MEMBER);
        }
        
        //Ověř, zda již taková pozvánka v databázi neexistuje
        $result = Db::fetchQuery('SELECT pozvanky_id FROM pozvanky WHERE uzivatele_id = ? AND tridy_id = ? LIMIT 1', array($user['id'], $this->id));
        if (empty($result))
        {
            //Nová pozvánka
            $invitation = new Invitation(true);
        }
        else
        {
            //Prodloužit životnost existující pozvánky
            $invitation = new Invitation(false, $result['pozvanky_id']);
        }
        
        $expiration = new DateTime('@'.(time() + Invitation::INVITATION_LIFETIME));
        $invitation->initialize($user, $this, $expiration);
        $invitation->save();
        
        return true;
    }
    
    /**
     * Metoda přidávající uživatele do třídy (přidává spojení uživatele a třídy do tabulky "clenstvi")
     * Pokud je tato třída veřejná nebo uzamčená, nic se nestane
     * @param int $userId ID uživatele získávajícího členství
     * @return boolean TRUE, pokud je členství ve třídě úspěšně přidáno, FALSE, pokud ne
     */
    public function addMember(int $userId)
    {
        //Zkontroluj, zda je třída soukromá
        //Není třeba - před zavoláním této metody při získávání členství pomocí kódu je zkontrolováno, zda není třída zamknutá
      # if (!$this->getStatus() === self::CLASS_STATUS_PRIVATE)
      # {
      #     //Nelze získat členství ve veřejné nebo uzamčené třídě
      #     return false;
      # }
        
        Db::connect();
        
        //Zkontroluj, zda již uživatel není členem této třídy
        //Není třeba - metoda getNewClassesByAccessCode ve třídě ClassManager navrací pouze třídy, ve kterých přihlášený uživatel ještě není členem
      # if ($this->checkAccess($userId))
      # {
      #     //Nelze získat členství ve třídě vícekrát
      #     return false;
      # }
        
        if (Db::executeQuery('INSERT INTO clenstvi (uzivatele_id,tridy_id) VALUES (?,?)', array($userId, $this->id)))
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
        $result = Db::fetchQuery('SELECT uzivatele.uzivatele_id, uzivatele.jmeno, uzivatele.email, uzivatele.posledni_prihlaseni, uzivatele.pridane_obrazky, uzivatele.uhodnute_obrazky, uzivatele.karma, uzivatele.status FROM tridy JOIN '.User::TABLE_NAME.' ON tridy.spravce = uzivatele.uzivatele_id WHERE tridy_id = ?;', array($this->id), false);
        $admin = new User(false, $result['uzivatele_id']);
        $admin->initialize($result['jmeno'], $result['email'], new DateTime($result['posledni_prihlaseni']), $result['pridane_obrazky'], $result['uhodnute_obrazky'], $result['karma'], $result['status']);
        $this->admin = $admin;
    }
    
    /**
     * Metoda ukládající do databáze nový požadavek na změnu názvu této třídy vyvolaný správcem této třídy, pokud žádný takový požadavek neexistuje nebo aktualizující stávající požadavek
     * Data jsou předem ověřena
     * @param string $newName Požadovaný nový název
     * @throws AccessDeniedException Pokud jméno nevyhovuje podmínkám systému
     * @return boolean TRUE, pokud je žádost úspěšně vytvořena/aktualizována
     */
    public function requestNameChange(string $newName)
    {
        if (mb_strlen($newName) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NAME_CHANGE_NO_NAME);}
        
        //Kontrola délky názvu
        $validator = new DataValidator();
        try
        {
            $validator->checkLength($newName, 5, 31, 3);
        }
        catch(RangeException $e)
        {
            if ($e->getMessage() === 'long')
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NAME_CHANGE_NAME_TOO_LONG, null, $e);
            }
            else if ($e->getMessage() === 'short')
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NAME_CHANGE_NAME_TOO_SHORT, null, $e);
            }
        }
        
        //Kontrola znaků v názvu
        try
        {
            $validator->checkCharacters($newName, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-', 0);
        }
        catch (InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NAME_CHANGE_INVALID_CHARACTERS, null, $e);
        }
        
        //Kontrola dostupnosti jména
        try
        {
            $validator->checkUniqueness($newName, 3);
        }
        catch (InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NAME_CHANGE_DUPLICATE_NAME, null, $e);
        }
        
        //Kontrola dat OK
        
        //Zkontrolovat, zda již existuje žádost o změnu názvu této třídy
        $applications = Db::fetchQuery('SELECT zadosti_jmena_tridy_id FROM zadosti_jmena_tridy WHERE tridy_id = ? LIMIT 1', array($this->id));
        if (!empty($applications['zadosti_jmena_tridy_id']))
        {
            //Přepsání existující žádosti
            Db::executeQuery('UPDATE zadosti_jmena_tridy SET nove = ?, cas = NOW() WHERE zadosti_jmena_tridy_id = ? LIMIT 1', array($newName, $applications['zadosti_jmena_tridy_id']));
        }
        else
        {
            //Uložení nové žádosti
            Db::executeQuery('INSERT INTO zadosti_jmena_tridy (tridy_id,nove,cas) VALUES (?,?,NOW())', array($this->id, $newName));
        }
        return true;
    }
    
    /**
     * Metoda upravující přístupová data této třídy z rozhodnutí administrátora
     * @param string $status Nový status třídy (musí být jedna z konstant této třídy)
     * @param int|NULL $code Nový přístupový kód třídy (nepovinné, pokud je status nastaven na "public" nebo "locked")
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem nebo jsou zadaná data neplatná
     * @return boolean TRUE, pokud jsou přístupová data třídy úspěšně aktualizována
     */
    public function updateAccessDataAsAdmin(string $status, $code)
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
        
        $validator = new DataValidator();
        //Kontrola platnosti dat
        if (($code !== null && !($validator->validateClassCode($code))) || !($status === self::CLASS_STATUS_PUBLIC || $status === self::CLASS_STATUS_PRIVATE || $status === self::CLASS_STATUS_LOCKED))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ADMINISTRATION_CLASS_UPDATE_INVALID_DATA);
        }
        
        //Kontrola dat OK
        
        Db::connect();
        Db::executeQuery('UPDATE tridy SET status = ?, kod = ? WHERE tridy_id = ? LIMIT 1;', array($status, $code, $this->id), false);
        
        return true;
    }
    
    /**
     * Metoda upravující přístupová data této třídy po jejich změně správcem třídy
     * @param string $status Nový status třídy (musí být jedna z konstant této třídy)
     * @param int|NULL $code Nový přístupový kód třídy (nepovinné, pokud je status nastaven na "public" nebo "locked")
     * @throws AccessDeniedException Pokud jsou zadaná data neplatná
     * @return boolean TRUE, pokud jsou přístupová data třídy úspěšně aktualizována
     */
    public function updateAccessData(string $status, $code)
    {
        //Nastavení kódu na NULL, pokud je třída nastavená na status, ve kterém by neměl smysl
        if ($status === self::CLASS_STATUS_PUBLIC || $status === self::CLASS_STATUS_LOCKED)
        {
            $code = null;
        }
        
        //Kontrola platnosti dat
        $validator = new DataValidator();
        if (!($status === self::CLASS_STATUS_PUBLIC || $status === self::CLASS_STATUS_PRIVATE || $status === self::CLASS_STATUS_LOCKED))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_ACCESS_CHANGE_INVALID_STATUS);
        }
        if ($code !== null && !($validator->validateClassCode($code)))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_ACCESS_CHANGE_INVALID_CODE);
        }
        
        //Kontrola dat OK
        
        Db::connect();
        Db::executeQuery('UPDATE tridy SET status = ?, kod = ? WHERE tridy_id = ? LIMIT 1;', array($status, $code, $this->id), false);
        
        return true;
    }
    
    /**
     * Metoda měnící správce této třídy z rozhodnutí administrátora
     * @param User $newAdmin Instance třídy uživatele reprezentující nového správce
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem 
     * @return boolean TRUE, pokud jsou přístupová data třídy úspěšně aktualizována
     */
    public function changeClassAdminAsAdmin(User $newAdmin)
    {
        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        if (!AccessChecker::checkSystemAdmin())
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
        
        //Kontrola dat OK (zda uživatel s tímto ID exisutje je již zkontrolováno v Administration::changeClassAdmin())
        
        Db::connect();
        Db::executeQuery('UPDATE tridy SET spravce = ? WHERE tridy_id = ? LIMIT 1;', array($newAdmin['id'], $this->id));
        
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

