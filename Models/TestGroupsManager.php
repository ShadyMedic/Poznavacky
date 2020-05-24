<?php
/** 
 * Třída získávající seznamy tříd, skupin a částí
 * @author Jan Štěch
 */
class TestGroupsManager
{
    public const CLASS_STATUS_PUBLIC = 'public';
    public const CLASS_MANAGE_BUTTON_KEYWORD = 'admin';
    public const CLASS_LEAVE_BUTTON_KEYWORD = 'leave';
    
    /**
     * Metoda pro získání seznamu všech tříd a vytvoření tabulky pro předání pohledu
     * @return array Dvourozměrné pole obsahující seznam tříd a další informace potřebné pro pohled
     */
    public static function getClasses()
    {
        //Získej data
        Db::connect();
        $classes = Db::fetchQuery('SELECT nazev,skupiny,status,spravce FROM `tridy` WHERE status = "public" OR tridy_id IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = ?);', array(UserManager::getId()), true);
        if (!$classes)
        {
            throw new NoDataException(NoDataException::NO_CLASSES, null, null, 0);
        }
        
        //Vytvoř tabulku
        $table = array();
        foreach ($classes as $dataRow)
        {
            $tableRow = array();
            $tableRow['rowLink'] = rtrim($_SERVER['REQUEST_URI'], '/').'/'.urlencode($dataRow['nazev']);
            $tableRow[0] = $dataRow['nazev'];
            $tableRow[1] = $dataRow['skupiny'];
            //Tlačítko pro správu třídy, pokud je přihlášený uživatel správcem třídy
            if (UserManager::getId() === $dataRow['spravce'])
            {
                $tableRow[2] = self::CLASS_MANAGE_BUTTON_KEYWORD;
            }
            //Tlačítko pro opuštění třídy, pokud není třída veřejná
            else if ($dataRow['status'] !== self::CLASS_STATUS_PUBLIC)
            {
                $tableRow[2] = self::CLASS_LEAVE_BUTTON_KEYWORD;
            }
            else
            {
                $tableRow[2] = '';
            }
            
            array_push($table, $tableRow);
        }
        
        return $table;
    }
    
    /**
     * Metoda pro získání seznamu poznávaček v určité třídě a vytvoření tabulky pro předání pohledu
     * @param string $className Název třídy ze které je potřeba získat seznam poznávaček
     * @return array Dvourozměrné pole obsahující seznam poznávaček a další informace potřebné pro pohled
     */
    public static function getGroups(string $className)
    {
        //Zkontroluj, zda třída existuje
        if (!ClassManager::classExists($className))
        {
            throw new NoDataException(NoDataException::UNKNOWN_CLASS);
        }
        
        $class = new ClassObject(0, $className);
        if ($class->checkAccess(UserManager::getId()))
        {   
            //Získej data
            $groups = $class->getGroups();
            if (empty($groups))
            {
                throw new NoDataException(NoDataException::NO_GROUPS, null, null, 1);
            }
            
            //Vytvoř tabulku
            $table = array();
            foreach ($groups as $group)
            {
                $tableRow = array();
                $tableRow['rowLink'] = rtrim($_SERVER['REQUEST_URI'], '/').'/'.urlencode($group->getName());
                $tableRow[0] = $group->getName();
                $tableRow[1] = $group->getPartsCount();
                
                array_push($table, $tableRow);
            }
        }
        else
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_MEMBER_IN_CLASS, null, null, array('originFile' => 'TestGroupsManager.php', 'displayOnView' => 'menu.phtml', 'menuTableLevel' => 1));
        }
        
        return $table;
    }
    
    /**
     * Metoda pro získání seznamu částí určité poznávačky v určité třídě a vytvoření tabulky pro předání pohledu
     * @param string $className Název třídy v níž se nachází poznávačka, ze které je potřeba získat seznam částí
     * @param string $testName Nátev poznávačky, ze které je potřeba získat seznam částí
     * @return array Dvourozměrné pole obsahující seznam částí a další informace potřebné pro pohled
     */
    public static function getParts(string $className, string $groupName)
    {
        //Zkontroluj, zda třída existuje
        if (!ClassManager::classExists($className))
        {
            throw new NoDataException(NoDataException::UNKNOWN_CLASS);
        }
        
        //Zkontroluj, zda poznávačka existuje
        $class = new ClassObject(0, $className);
        if (!$class->groupExists($groupName))
        {
            throw new NoDataException(NoDataException::UNKNOWN_GROUP);
        }
        
        $group = new Group(0, $groupName, $class);
        if ($class->checkAccess(UserManager::getId(), $class->getId()))
        {
            //Získej data
            $parts = $group->getParts();
            if (empty($parts))
            {
                throw new NoDataException(NoDataException::NO_PARTS, null, null, 2);
            }
            
            //Vytvoř tabulku
            $totalNaturals = 0;
            $totalPictures = 0;
            $table = array();
            foreach ($parts as $part)
            {
                $tableRow = array();
                $tableRow['rowLink'] = rtrim($_SERVER['REQUEST_URI'], '/').'/'.urlencode($part->getName());
                $tableRow[0] = $part->getName();
                $tableRow[1] = $part->getNaturalsCount();
                $tableRow[2] = $part->getPicturesCount();
                
                $totalNaturals += $part->getNaturalsCount();
                $totalPictures += $part->getPicturesCount();
                
                array_push($table, $tableRow);
            }
            //Přidej řádku pro výběr všech částí, pokud jich existuje více
            if (count($parts) > 1)
            {
                $tableRow = array();
                $tableRow['rowLink'] = ltrim($_SERVER['REQUEST_URI'], '/');
                $tableRow[0] = 'Vše';
                $tableRow[1] = $totalNaturals;
                $tableRow[2] = $totalPictures;
                
                array_push($table, $tableRow);
            }
        }
        else
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_HAVING_ACCESS_TO_GROUP, null, null, array('originFile' => 'TestGroupsManager.php', 'displayOnView' => 'menu.phtml', 'menuTableLevel' => 2));
        }
        
        return $table;
    }
}