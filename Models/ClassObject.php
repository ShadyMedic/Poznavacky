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
    
    /**
     * Konstruktor třídy nastavující její ID a jméno. Pokud je specifikováno ID i jméno, má ID přednost
     * @param int $id ID třídy (nepovinné, pokud je specifikováno jméno)
     * @param string $name Jméno třídy (nepovinné, pokud je specifikováno ID)
     * @throws BadMethodCallException
     */
    public function __construct(int $id, string $name = "")
    {
        if (mb_strlen($name) !== 0)
        {
            $id = ClassManager::getIdByName($name);
        }
        else if (!empty($id))
        {
            $name = ClassManager::getNameById($id);
        }
        else
        {
            throw new BadMethodCallException('At least one of the arguments must be specified.', null, null);
        }
        $this->id = $id;
        $this->name = $name;
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
     * Metoda kontrolující, zda má určitý uživatel přístup do této třídy
     * @param int $userId ID ověřovaného uživatele
     * @return boolean TRUE, pokud má uživatel přístup do třídy, FALSE pokud ne
     */
    public function checkAccess(int $userId)
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM `tridy` WHERE tridy_id = ? AND (status = "public" OR tridy_id IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = ?));', array($this->id, $userId), false);
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

