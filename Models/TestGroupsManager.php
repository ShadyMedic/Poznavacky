<?php

/** 
 * Třída získávající seznamy tříd, skupin a částí
 * @author Jan Štěch
 */
class TestGroupsManager
{
    /**
     * Metoda pro získání seznamu všech tříd
     * @return array Dvourozměrné pole obsahující seznam tříd
     */
    static function getClasses()
    {
        Db::connect();
        return Db::fetchQuery('SELECT * FROM `tridy` WHERE status = "public" OR tridy_id IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = ?);', array(UserManager::getId()), true);
    }
    
    /**
     * Metoda pro získání seznamu poznávaček v určité třídě
     * @param string $className Název třídy ze které je potřeba získat seznam poznávaček
     * @return array Dvourozměrné pole obsahující seznam poznávaček
     */
    static function getGroups(string $className)
    {
        if (AccessChecker::checkAccess(UserManager::getId(), ClassManager::getId($className)))
        {
            Db::connect();
            return Db::fetchQuery('SELECT * FROM poznavacky WHERE tridy_id = (SELECT tridy_id FROM tridy WHERE nazev = ?);', array($className), true);
        }
        else
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_MEMBER_IN_CLASS, null, null, array('originFile' => 'TestGroupsManager.php', 'displayOnView' => 'menu.phtml', 'menuTableLevel' => 1));
        }
    }
    
    /**
     * Metoda pro získání seznam částí určité poznávačky v určité třídě
     * @param string $className Název třídy v níž se nachází poznávačka, ze které je potřeba získat seznam částí
     * @param string $testName Nátev poznávačky, ze které je potřeba získat seznam částí
     * @return array Dvourozměrné pole obsahující seznam částí
     */
    static function getParts(string $className, string $groupName)
    {
        if (AccessChecker::checkAccess(UserManager::getId(), ClassManager::getId($name)))
        {        
            Db::connect();
            return Db::fetchQuery('SELECT * FROM casti WHERE poznavacky_id = (SELECT poznavacky_id FROM poznavacky WHERE nazev = ?) AND tridy_id = (SELECT tridy_id FROM tridy WHERE nazev = ?);', array($testName, $className), true);
        }
        else
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_HAVING_ACCESS_TO_GROUP, null, null, array('originFile' => 'TestGroupsManager.php', 'displayOnView' => 'menu.phtml', 'menuTableLevel' => 2));
        }
    }
}