<?php
/** 
 * Třída získávající seznamy tříd, skupin a částí
 * @author Jan Štěch
 */
class TestGroupsFetcher
{
    public const CLASS_STATUS_PUBLIC = 'public';
    public const CLASS_MANAGE_BUTTON_KEYWORD = 'admin';
    public const CLASS_LEAVE_BUTTON_KEYWORD = 'leave';
    
    /**
     * Metoda pro získání seznamu všech tříd a vytvoření tabulky pro předání pohledu
     * @return array Dvourozměrné pole obsahující seznam tříd a další informace potřebné pro pohled
     */
    public function getClasses(): array
    {
        //Získej data
        $classes = Db::fetchQuery('SELECT '.ClassObject::COLUMN_DICTIONARY['name'].','.ClassObject::COLUMN_DICTIONARY['url'].','.ClassObject::COLUMN_DICTIONARY['groupsCount'].','.ClassObject::COLUMN_DICTIONARY['status'].','.ClassObject::COLUMN_DICTIONARY['admin'].' FROM '.ClassObject::TABLE_NAME.' WHERE '.ClassObject::COLUMN_DICTIONARY['status'].' = "public" OR '.ClassObject::COLUMN_DICTIONARY['id'].' IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = ?);', array(UserManager::getId()), true);
        if (!$classes)
        {
            throw new NoDataException(NoDataException::NO_CLASSES, null, null, 0);
        }
        
        //Vytvoř tabulku
        $table = array();
        foreach ($classes as $dataRow)
        {
            $tableRow = array();
            $tableRow['rowLink'] = rtrim($_SERVER['REQUEST_URI'], '/').'/'.$dataRow[ClassObject::COLUMN_DICTIONARY['url']];
            $tableRow[0] = $dataRow[ClassObject::COLUMN_DICTIONARY['name']];
            $tableRow[1] = $dataRow[ClassObject::COLUMN_DICTIONARY['groupsCount']];
            //Tlačítko pro správu třídy, pokud je přihlášený uživatel správcem třídy
            if (UserManager::getId() === $dataRow[ClassObject::COLUMN_DICTIONARY['admin']])
            {
                $tableRow[2] = self::CLASS_MANAGE_BUTTON_KEYWORD;
            }
            //Tlačítko pro opuštění třídy, pokud není třída veřejná
            else if ($dataRow[ClassObject::COLUMN_DICTIONARY['status']] !== self::CLASS_STATUS_PUBLIC)
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
     * @param ClassObject $class Objekt třídy ze které je potřeba získat seznam poznávaček
     * @return array Dvourozměrné pole obsahující seznam poznávaček a další informace potřebné pro pohled
     */
    public function getGroups(ClassObject $class): array
    {
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
                $tableRow['rowLink'] = rtrim($_SERVER['REQUEST_URI'], '/').'/'.$group->getUrl();
                $tableRow[0] = $group->getName();
                $tableRow[1] = $group->getPartsCount();
                
                array_push($table, $tableRow);
            }
        }
        else
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_MEMBER_IN_CLASS, null, null);
        }
        
        return $table;
    }
    
    /**
     * Metoda pro získání seznamu částí určité poznávačky v určité třídě a vytvoření tabulky pro předání pohledu
     * @param Group $group Objekt poznávačky, ze které je potřeba získat seznam částí
     * @return array Dvourozměrné pole obsahující seznam částí a další informace potřebné pro pohled
     */
    public function getParts(Group $group): array
    {
        if ($group->getClass()->checkAccess(UserManager::getId()))
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
                $tableRow['rowLink'] = rtrim($_SERVER['REQUEST_URI'], '/').'/'.urlencode($part->getUrl());
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
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_HAVING_ACCESS_TO_GROUP, null, null);
        }
        
        return $table;
    }
}