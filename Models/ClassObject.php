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
    private $groups;
    
    private $accessCheckResult;
    
    /**
     * Konstruktor třídy nastavující její ID a jméno.
     * Pokud je vše specifikováno, nebude potřeba provádět další SQL dotazy
     * Pokud je vyplněno jméno i ID, ale chybí nějaký z dalších argumentů, má jméno přednost před ID
     * @param int $id ID třídy (nepovinné, pokud je specifikováno jméno)
     * @param string $name Jméno třídy (nepovinné, pokud je specifikováno ID)
     * @param string $statis Status třídy (musí mít hodnotu jako některá z konstant této třídy; nepovinné, v případě nevyplnění bude načteno z databáze až v případě potřeby)
     * @throws BadMethodCallException
     */
    public function __construct(int $id, string $name = "", string $status = "")
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
     */
    private function loadGroups()
    {
        $this->groups = array();
        
        Db::connect();
        $result = Db::fetchQuery('SELECT poznavacky_id,nazev,casti FROM poznavacky WHERE tridy_id = ?', array($this->id), true);
        foreach ($result as $groupData)
        {
            $this->groups[] = new Group($groupData['poznavacky_id'], $groupData['nazev'], $this, $groupData['casti']);
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
        
        Db::connect();
        $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM `tridy` WHERE tridy_id = ? AND (status = "public" OR tridy_id IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = ?));', array($this->id, $userId), false);
        $this->accessCheckResult = ($result['cnt'] === 1) ? true : false;
        return ($result['cnt'] === 1) ? true : false;
    }
    
    /**
     * Metoda kontrolující, zda je určitý uživatel správcem této třídy
     * @param int $userId ID ověřovaného uživatele
     * @return boolean TRUE, pokud je uživatelem správce třídy, FALSE pokud ne
     */
    public function checkAdmin(int $userId)
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM `tridy` WHERE tridy_id = ? AND spravce = ?;', array($this->id, $userId), false);
        return ($result['cnt'] === 1) ? true : false;
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
        if (!$this->status === self::CLASS_STATUS_PRIVATE)
        {
            //Nelze získat členství ve veřejné nebo uzamčené třídě
            return false;
        }
        
        Db::connect();
        
        //Zkontroluj, zda již uživatel není členem této třídy
        if ($this->checkAccess($userId))
        {
            //Nelze získat členství ve třídě vícekrát
            return false;
        }
        
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
    private function getStatus()
    {
        if (isset($this->status))
        {
            return $this->status;
        }
        return $this->loadStatus();
    }
    
    /**
     * Metoda získávající z databáze status této třídy a nastavující jej jako vlastnost "status"
     * @return string Status třídy (viz konstanty třídy)
     */
    private function loadStatus()
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT status FROM tridy WHERE tridy_id = ? LIMIT 1', array($this->id), false);
        if ($result)
        {
            $this->status = $result['status'];
            return $result['status'];
        }
    }
}

