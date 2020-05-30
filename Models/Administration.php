<?php

/** 
 * Třída získávající informace pro stránku se správou systému
 * V případě, že se tato třída příliš rozroste bude lepší ji rozdělit na více tříd
 * @author Jan Štěch
 */
class Administration
{
    /**
     * Metoda navracející většinu informací o všech uživatelích v databázi
     * @param bool $includeLogged TRUE, pokud má být navrácen i záznam přihlášeného uživatele
     * @return User[] Pole instancí třídy User
     */
    public function getAllUsers(bool $includeLogged = true)
    {
        Db::connect();
        if ($includeLogged)
        {
            $dbResult = Db::fetchQuery('SELECT uzivatele_id,jmeno,email,posledni_prihlaseni,pridane_obrazky,uhodnute_obrazky,karma,status FROM uzivatele', array(), true);
        }
        else
        {
            $dbResult = Db::fetchQuery('SELECT uzivatele_id,jmeno,email,posledni_prihlaseni,pridane_obrazky,uhodnute_obrazky,karma,status FROM uzivatele WHERE uzivatele_id != ?', array(UserManager::getId()), true);
        }
        $users = array();
        foreach($dbResult as $dbRow)
        {
            $lastLogin = new DateTime($dbRow['posledni_prihlaseni']);
            $users[] = new User($dbRow['uzivatele_id'], $dbRow['jmeno'], $dbRow['email'], $lastLogin, $dbRow['pridane_obrazky'], $dbRow['uhodnute_obrazky'], $dbRow['karma'], $dbRow['status']);
        }
        
        return $users;
    }
}