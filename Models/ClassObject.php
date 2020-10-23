<?php
/**
 * Třída reprezentující objekt třídy (jakože té z reálného světa / obsahující poznávačky)
 * @author Jan Štěch
 */
class ClassObject extends DatabaseItem
{
    public const TABLE_NAME = 'tridy';
    
    public const COLUMN_DICTIONARY = array(
        'id' => 'tridy_id',
        'name' => 'nazev',
        'status' => 'status',
        'code' => 'kod',
        'groupsCount' => 'poznavacky',
        'admin' => 'spravce'
    );
    
    protected const NON_PRIMITIVE_PROPERTIES = array(
        'admin' => User::class
    );
    
    protected const DEFAULT_VALUES = array(
        'status' => self::CLASS_STATUS_PRIVATE,
        'code' => 0,
        'groups' => array(),
        'groupsCount' => 0,
        'members' => array()
    );
    
    protected const CAN_BE_CREATED = false;
    protected const CAN_BE_UPDATED = true;
    
    public const CLASS_STATUS_PUBLIC = "public";
    public const CLASS_STATUS_PRIVATE = "private";
    public const CLASS_STATUS_LOCKED = "locked";
    
    public const CLASS_STATUSES_DICTIONARY = array(
        'veřejná' => self::CLASS_STATUS_PUBLIC,
        'soukromá' => self::CLASS_STATUS_PRIVATE,
        'uzamčená' => self::CLASS_STATUS_LOCKED
    );
    
    protected $name;
    protected $status;
    protected $code;
    protected $groupsCount;
    protected $admin;
    
    protected $members;
    protected $groups;
    
    private $accessCheckResult;
    
    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * Při nastavení některého z argumentů na undefined, je hodnota dané vlastnosti také nastavena na undefined
     * Při nastavení některého z argumentů na null, není hodnota dané vlastnosti nijak pozměněna
     * @param string|undefined|null $name Název třídy
     * @param string|undefined|null $status Status přístupnosti třídy (musí být jedna z konstant této třídy začínající na CLASS_STATUS_)
     * @param int|undefined|null $code Přístupový kód třídy
     * @param Group[]|undefined|null $groups Pole poznávaček patřících do této třídy jako objekty
     * @param int|undefined|null $groupsCount Počet poznávaček patřících do této třídy (při vyplnění parametru $groups je ignorováno a je použita délka poskytnutého pole)
     * @param User[]|undefined|null $members Pole uživatelů, kteří mají členství v této třídě
     * @param User|undefined|null $admin Odkaz na objekt uživatele, který je správcem této třídy
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($name = null, $status = null, $code = null, $groups = null, $groupsCount = null, $members = null, $admin = null)
    {        
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($name === null){ $name = $this->name; }
        if ($status === null){ $status = $this->status; }
        if ($code === null){ $code = $this->code; }
        if ($groups === null)
        {
            $groups = $this->groups;
            if ($groupsCount === null){ $groupsCount = $this->groupsCount; }
        }
        else { $groupsCount = count($groups); }
        if ($members === null){ $members = $this->members; }
        if ($admin === null){ $admin = $this->admin; }
        
        $this->name = $name;
        $this->status = $status;
        $this->code = $code;
        $this->groups = $groups;
        $this->groupsCount = $groupsCount;
        $this->members = $members;
        $this->admin = $admin;
    }
    
    /**
     * Metoda navracející jméno této třídy
     * @return string Jméno třídy
     */
    public function getName()
    {
        $this->loadIfNotLoaded($this->name);
        return $this->name;
    }
    
    /**
     * Metoda navracející počet poznávaček v této třídě
     * @return int Počet poznávaček
     */
    public function getGroupsCount()
    {
        $this->loadIfNotLoaded($this->groupsCount);
        return $this->groupsCount;
    }
    
    /**
     * Metoda navracející uložený status této třídy.
     * @return string Status třídy (viz konstanty třídy)
     */
    public function getStatus()
    {
        $this->loadIfNotLoaded($this->status);
        return $this->status;
    }
    
    /**
     * Metoda navracející uložený vstupní kód této třídy
     * @return int Čtyřmístný kód této třídy
     */
    public function getCode()
    {
        $this->loadIfNotLoaded($this->code);
        return $this->code;
    }
    
    /**
     * Metoda pro získání objektu uživatele, který je správcem této třídy
     * @return User Objekt správce třídy
     */
    public function getAdmin()
    {
        $this->loadIfNotLoaded($this->admin);
        return $this->admin;
    }
    
    /**
     * Metoda navracející pole poznávaček patřících do této třídy jako objekty
     * Pokud zatím nebyly poznávačky načteny, budou načteny z databáze
     * @return Group[] Pole poznávaček patřících do této třídy jako objekty
     */
    public function getGroups()
    {
        if (!$this->isDefined($this->groups))
        {
            $this->loadGroups();
        }
        return $this->groups;
    }
    
    /**
     * Metoda načítající poznávačky patřící do této třídy a ukládající je jako vlastnosti do pole jako objekty
     * Počet poznávaček v této třídě je také aktualizován (vlastnost ClassObject::$groupsCount)
     */
    public function loadGroups()
    {
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        $result = Db::fetchQuery('SELECT '.Group::COLUMN_DICTIONARY['id'].','.Group::COLUMN_DICTIONARY['name'].','.Group::COLUMN_DICTIONARY['partsCount'].' FROM '.Group::TABLE_NAME.' WHERE '.Group::COLUMN_DICTIONARY['class'].' = ?', array($this->id), true);
        if ($result === false || count($result) === 0)
        {
            //Žádné poznávačky nenalezeny
            $this->groups = array();
        }
        else
        {
            $this->groups = array();
            foreach ($result as $groupData)
            {
                $group = new Group(false, $groupData[Group::COLUMN_DICTIONARY['id']]);
                $group->initialize($groupData[Group::COLUMN_DICTIONARY['name']], $this, null, $groupData[Group::COLUMN_DICTIONARY['partsCount']]);
                $this->groups[] = $group;
            }
        }
        
        $this->groupsCount = count($this->groups);
    }
    
    /**
     * Metoda přidávající do databáze i do instance třídy novou poznávačku
     * @param string $groupName Ošetřený název nové poznávačky
     * @return boolean TRUE, pokud je poznávačka vytvořena a přidána úspěšně, FALSE, pokud ne
     */
    public function addGroup(string $groupName)
    {
        if (!$this->isDefined($this->groups))
        {
            $this->loadGroups();
        }
        
        $group = new Group(true);
        $group->initialize($groupName, $this, null, 0);
        try
        {
            $result = $group->save();
            if ($result)
            {
                $this->groups[] = $group;
                return true;
            }
        }
        catch (BadMethodCallException $e) { }
        
        return false;
    }
    
    /**
     *
     * Metoda odstraňující danou poznávačku z této třídy i z databáze
     * @param Group $group Objekt poznávačky, který má být odstraněn
     * @throws BadMethodCallException Pokud daná poznávačka není součástí této třídy
     * @return boolean TRUE, v případě, že se odstranění poznávačky povede, FALSE, pokud ne
     */
    public function removeGroup(Group $group)
    {
        if (!$this->isDefined($this->groups))
        {
            $this->loadGroups();
        }
        
        //Získání indexu, pod kterým je uložena odstraňovaná poznávačka
        for ($i = 0; $i < count($this->groups) && $this->groups[$i]->getId() != $group->getId(); $i++) { }
        
        if ($i === count($this->groups))
        {
            throw new BadMethodCallException("Tato poznávačka není součástí této třídy a tudíž z ní nemůže být odstraněna");
        }
        
        //Odebrání odkazu na poznávačku z této instance třídy
        array_splice($this->groups, $i, 1);
        
        //Odstranění poznávačky z databáze
        $group->delete();
    }
    
    /**
     * Metoda navracející pole členů této třídy jako objekty
     * Pokud zatím nebyly členové načteny, budou načteny z databáze
     * @return User[] Pole členů patřících do této třídy jako instance třídy User
     */
    public function getMembers()
    {
        if (!$this->isDefined($this->members))
        {
            $this->loadMembers();
        }
        return $this->members;
    }
    
    /**
     * Metoda načítající členy patřící do této třídy a ukládající je jako vlastnosti do pole jako objekty
     * Přihlášený uživatel není zahrnut do tohoto pole, i když je třeba členem třídy
     */
    public function loadMembers()
    {
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        $result = Db::fetchQuery('SELECT '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['id'].','.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['name'].','.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['email'].','.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['lastLogin'].','.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['addedPictures'].','.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['guessedPictures'].','.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['karma'].','.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['status'].' FROM clenstvi JOIN '.User::TABLE_NAME.' ON clenstvi.uzivatele_id = '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['id'].' WHERE clenstvi.tridy_id = ? AND '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['id'].' != ? ORDER BY '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['lastLogin'].' DESC;', array($this->id, UserManager::getId()), true);
        if ($result === false || count($result) === 0)
        {
            //Žádní členové nenalezeni
            $this->members = array();
        }
        else
        {
            $this->members = array();
            foreach ($result as $memberData)
            {
                $user = new User(false, $memberData[User::COLUMN_DICTIONARY['id']]);
                $user->initialize($memberData[User::COLUMN_DICTIONARY['name']], $memberData[User::COLUMN_DICTIONARY['email']], new DateTime($memberData[User::COLUMN_DICTIONARY['lastLogin']]), $memberData[User::COLUMN_DICTIONARY['addedPictures']], $memberData[User::COLUMN_DICTIONARY['guessedPictures']], $memberData[User::COLUMN_DICTIONARY['karma']], $memberData[User::COLUMN_DICTIONARY['status']]);
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
        $this->loadIfNotLoaded($this->id);
        
        //Zkontroluj, zda tato třída není veřejná
        if ($this->status === self::CLASS_STATUS_PUBLIC)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_INVITE_USER_PUBLIC_CLASS);
        }
        
        //Konstrukce objektu uživatele
        Db::connect();
        $result = Db::fetchQuery('SELECT '.User::COLUMN_DICTIONARY['id'].','.User::COLUMN_DICTIONARY['name'].','.User::COLUMN_DICTIONARY['email'].','.User::COLUMN_DICTIONARY['lastLogin'].','.User::COLUMN_DICTIONARY['addedPictures'].','.User::COLUMN_DICTIONARY['guessedPictures'].','.User::COLUMN_DICTIONARY['karma'].','.User::COLUMN_DICTIONARY['status'].' FROM '.User::TABLE_NAME.' WHERE '.User::COLUMN_DICTIONARY['name'].' = ? LIMIT 1', array($userName));
        if (empty($result))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_INVITE_USER_UNKNOWN_USER);
        }
        $user = new User(false, $result[User::COLUMN_DICTIONARY['id']]);
        $user->initialize($result[User::COLUMN_DICTIONARY['name']], $result[User::COLUMN_DICTIONARY['email']], new DateTime($result[User::COLUMN_DICTIONARY['lastLogin']]), $result[User::COLUMN_DICTIONARY['addedPictures']], $result[User::COLUMN_DICTIONARY['guessedPictures']], $result[User::COLUMN_DICTIONARY['karma']], $result[User::COLUMN_DICTIONARY['status']]);
        
        //Zkontroluj, zda uživatel již není členem třídy
        for ($i = 0; $i < count($this->members) && $user->getId() !== $this->members[$i]->getId(); $i++){}
        if ($i !== count($this->members))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_INVITE_USER_ALREADY_MEMBER);
        }
        
        //Ověř, zda již taková pozvánka v databázi neexistuje
        $result = Db::fetchQuery('SELECT '.Invitation::COLUMN_DICTIONARY['id'].' FROM '.Invitation::TABLE_NAME.' WHERE '.Invitation::COLUMN_DICTIONARY['user'].' = ? AND '.Invitation::COLUMN_DICTIONARY['class'].' = ? LIMIT 1', array($user->getId(), $this->id));
        if (empty($result))
        {
            //Nová pozvánka
            $invitation = new Invitation(true);
        }
        else
        {
            //Prodloužit životnost existující pozvánky
            $invitation = new Invitation(false, $result[Invitation::COLUMN_DICTIONARY['id']]);
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
        $this->loadIfNotLoaded($this->id);
        
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
     * @throws AccessDeniedException Pokud se jedná o veřejnou třídu, pokud uživatel s daným ID není členem této třídy nebo pokud se vyskytne chyba při odstraňování iživatelova členství z databáze
     * @return boolean TRUE, v případě, že se odstranění uživatele povede
     */
    public function removeMember(int $userId)
    {
        $this->loadIfNotLoaded($this->status);
        
        if ($this->status == self::CLASS_STATUS_PUBLIC)
        {
            //Nelze odstranit člena z veřejné třídy
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_KICK_USER_PUBLIC_CLASS);
        }
        
        $this->loadIfNotLoaded($this->id);
        if (!$this->isDefined($this->members)){ $this->loadMembers(); }
        
        //Odebrat uživatele z pole uživatelů v objektu třídy
        for ($i = 0; $i < count($this->members); $i++)
        {
            if ($this->members[$i]->getId() === $userId)
            {
                array_splice($this->members, $i, 1);
                $i = count($this->members) + 1;
            }
        }
        if ($i === count($this->members))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_KICK_USER_NOT_A_MEMBER);
        }
        
        //Odstranit členství z databáze
        Db::connect();
        if (Db::executeQuery('DELETE FROM clenstvi WHERE tridy_id = ? AND uzivatele_id = ? LIMIT 1', array($this->id, $userId)))
        {
            return true;
        }
        else
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_UNEXPECTED);
        }
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
        
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM '.self::TABLE_NAME.' WHERE '.self::COLUMN_DICTIONARY['id'].' = ? AND ('.self::COLUMN_DICTIONARY['status'].' = "public" OR '.self::COLUMN_DICTIONARY['id'].' IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = ?));', array($this->id, $userId), false);
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
        $this->loadIfNotLoaded($this->admin);
        return ($this->admin->getId() === $userId) ? true : false;
    }
    
    /**
     * Metoda kontrolující, zda v této třídě existuje specifikovaná poznávačka
     * @param string $groupName Jméno poznávačky
     * @return boolean TRUE, pokud byla poznávačka nalezene, FALSE, pokud ne
     */
    public function groupExists(string $groupName)
    {
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        $cnt = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM '.Group::TABLE_NAME.' WHERE '.Group::COLUMN_DICTIONARY['name'].' = ? AND '.Group::COLUMN_DICTIONARY['class'].' = ?', array($groupName, $this->id), false);
        if ($cnt['cnt'] > 0)
        {
            return true;
        }
        return false;
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
        
        $this->loadIfNotLoaded($this->id);
        
        //Zkontrolovat, zda již existuje žádost o změnu názvu této třídy
        $applications = Db::fetchQuery('SELECT '.ClassNameChangeRequest::COLUMN_DICTIONARY['id'].' FROM '.ClassNameChangeRequest::TABLE_NAME.' WHERE '.ClassNameChangeRequest::COLUMN_DICTIONARY['subject'].' = ? LIMIT 1', array($this->id));
        if (!empty($applications[ClassNameChangeRequest::COLUMN_DICTIONARY['id']]))
        {
            //Přepsání existující žádosti
            Db::executeQuery('UPDATE '.ClassNameChangeRequest::TABLE_NAME.' SET '.ClassNameChangeRequest::COLUMN_DICTIONARY['newName'].' = ?, '.ClassNameChangeRequest::COLUMN_DICTIONARY['requestedAt'].' = NOW() WHERE '.ClassNameChangeRequest::COLUMN_DICTIONARY['id'].' = ? LIMIT 1', array($newName, $applications['zadosti_jmena_tridy_id']));
        }
        else
        {
            //Uložení nové žádosti
            Db::executeQuery('INSERT INTO '.ClassNameChangeRequest::TABLE_NAME.' ('.ClassNameChangeRequest::COLUMN_DICTIONARY['subject'].','.ClassNameChangeRequest::COLUMN_DICTIONARY['newName'].','.ClassNameChangeRequest::COLUMN_DICTIONARY['requestedAt'].') VALUES (?,?,NOW())', array($this->id, $newName));
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
        
        $this->status = $status;
        $this->code = $code;
        
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET '.self::COLUMN_DICTIONARY['status'].' = ?, '.self::COLUMN_DICTIONARY['code'].' = ? WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1;', array($status, $code, $this->id), false);
        
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
        
        $this->status = $status;
        $this->code = $code;
        
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET '.self::COLUMN_DICTIONARY['status'].' = ?, '.self::COLUMN_DICTIONARY['code'].' = ? WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1;', array($status, $code, $this->id), false);
        
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
        
        $this->admin = $newAdmin;
        
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET '.self::COLUMN_DICTIONARY['admin'].' = ? WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1;', array($newAdmin->getId(), $this->id));
        
        return true;
    }
    
    /**
     * Metoda odstraňující tuto třídu z databáze na základě rozhodnutí administrátora
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
        
        $this->delete();
        
        return true;
    }
    
    /**
     * Metoda odstraňující tuto třídu z databáze na základě rozhodnutí jejího správce
     * @param string $password Heslo správce třídy pro ověření
     * @throws AccessDeniedException Pokud přihlášený uživatel není správcem této třídy nebo zadal špatné / žádné heslo
     * @return boolean TRUE, pokud je třída úspěšně odstraněna z databáze
     */
    public function deleteAsClassAdmin(string $password)
    {
        //Kontrola, zda je přihlášený uživatel správcem této třídy
        if (!$this->checkAdmin(UserManager::getId()))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
        
        //Kontrola hesla
        if (mb_strlen($password) === 0)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_NO_PASSWORD_GENERAL);
        }
        if (!AccessChecker::recheckPassword($password))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_WRONG_PASSWORD_GENERAL);
        }
        
        //Kontrola dat OK
        
        //Odstranit třídu z databáze
        $result = $this->delete();
        
        //Zrušit výběr této třídy (a jejích podčástí) v $_SESSION['selection']
        unset($_SESSION['selection']);
        
        return $result;
    }
    
    /**
     * Metoda odstraňující tuto třídu z databáze
     * @return boolean TRUE, pokud je třída úspěšně odstraněna z databáze
     * {@inheritDoc}
     * @see DatabaseItem::delete()
     */
    public function delete()
    {
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        Db::executeQuery('DELETE FROM '.self::TABLE_NAME.' WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1;', array($this->id));
        $this->id = new undefined();
        $this->savedInDb = false;
        return true;
    }
}

