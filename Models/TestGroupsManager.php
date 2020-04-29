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
        $classes = Db::fetchQuery('SELECT * FROM `tridy` WHERE status = "public" OR tridy_id IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = ?);', array(UserManager::getId()), true);
        if (!$classes)
        {
            throw new NoDataException(NoDataException::NO_CLASSES, null, null, 0);
        }
        return $classes;
    }
    
    /**
     * Metoda pro získání seznamu poznávaček v určité třídě
     * @param string $className Název třídy ze které je potřeba získat seznam poznávaček
     * @return array Dvourozměrné pole obsahující seznam poznávaček
     */
    static function getGroups(string $className)
    {
        if (AccessChecker::checkAccess(UserManager::getId(), ClassManager::getIdByName($className)))
        {
            Db::connect();
            $groups = Db::fetchQuery('SELECT * FROM poznavacky WHERE tridy_id = (SELECT tridy_id FROM tridy WHERE nazev = ?);', array($className), true);
            if (!$groups)
            {
                throw new NoDataException(NoDataException::NO_GROUPS, null, null, 1);
            }
            return $groups;
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
        if (AccessChecker::checkAccess(UserManager::getId(), ClassManager::getIdByName($className)))
        {        
            Db::connect();
            $parts = Db::fetchQuery('SELECT * FROM casti WHERE poznavacky_id = (SELECT poznavacky_id FROM poznavacky WHERE nazev = ?) AND tridy_id = (SELECT tridy_id FROM tridy WHERE nazev = ?);', array($groupName, $className), true);
            if (!$parts)
            {
                throw new NoDataException(NoDataException::NO_PARTS, null, null, 2);
            }
            return $parts;
        }
        else
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_HAVING_ACCESS_TO_GROUP, null, null, array('originFile' => 'TestGroupsManager.php', 'displayOnView' => 'menu.phtml', 'menuTableLevel' => 2));
        }
    }
}