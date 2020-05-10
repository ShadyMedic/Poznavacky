<?php
/**
 * Třída reprezentující objekt třídy (jakože té z reálného světa / obsahující poznávačky)
 * @author Jan Štěch
 */
class ClassObject
{
    private $id;
    private $name;
    
    /**
     * Konstruktor třídy nastavující její ID a jméno. Pokud je specifikováno ID i jméno, má ID přednost
     * @param int $id ID třídy (nepovinné, pokud je specifikováno jméno)
     * @param string $name Jméno třídy (nepovinné, pokud je specifikováno ID)
     * @throws BadMethodCallException
     */
    public function __construct(int $id, string $name = "")
    {
        if (!empty($name))
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
     * Metoda odstraňující uživatele ze třídy (odstraňuje spojení uživatele a třídy z tabulky "clenstvi")
     * Pokud je třída veřejná, nic se nestane
     * @param int $userId
     * @return boolean TRUE, v případě, že se odstranění uživatele povede, FALSE, pokud ne
     */
    public function removeMember(int $userId)
    {
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
}

