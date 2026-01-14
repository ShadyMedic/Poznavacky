<?php
namespace Poznavacky\Models;

use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\DatabaseItems\Group;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Exceptions\NoDataException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\Statics\UserManager;

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
     * @throws NoDataException Pokud nebyly nalezeny žádné třídy, do kterých by měl uživatel přístup
     * @throws DatabaseException
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     */
    public function getClasses(): array
    {
        //Získej data
        $chekcer = new AccessChecker();
        if ($chekcer->checkDemoAccount()) {
            //Nezahrnuj jiné než uzamčené třídy
            $classes = Db::fetchQuery('SELECT '.ClassObject::COLUMN_DICTIONARY['name'].','.
                                      ClassObject::COLUMN_DICTIONARY['url'].','.
                                      ClassObject::COLUMN_DICTIONARY['groupsCount'].','.
                                      ClassObject::COLUMN_DICTIONARY['status'].','.
                                      ClassObject::COLUMN_DICTIONARY['admin'].' FROM '.ClassObject::TABLE_NAME.
                                      ' WHERE '.ClassObject::COLUMN_DICTIONARY['status'].' = "locked" AND '.
                                      ClassObject::COLUMN_DICTIONARY['id'].
                                      ' IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = ?);',
                array(UserManager::getId()), true);
        } else {
            $classes = Db::fetchQuery('SELECT '.ClassObject::COLUMN_DICTIONARY['name'].','. //Třídy spravované uživatelem
                                      ClassObject::COLUMN_DICTIONARY['url'].','.
                                      ClassObject::COLUMN_DICTIONARY['groupsCount'].','.
                                      ClassObject::COLUMN_DICTIONARY['status'].','.
                                      ClassObject::COLUMN_DICTIONARY['admin'].' FROM '.ClassObject::TABLE_NAME.
                                      ' WHERE '.ClassObject::COLUMN_DICTIONARY['admin'].' = ?'.
                                      ' UNION '.
                                      'SELECT '.ClassObject::COLUMN_DICTIONARY['name'].','. //Neveřejné třídy v nichž je uživatel členem
                                      ClassObject::COLUMN_DICTIONARY['url'].','.
                                      ClassObject::COLUMN_DICTIONARY['groupsCount'].','.
                                      ClassObject::COLUMN_DICTIONARY['status'].','.
                                      ClassObject::COLUMN_DICTIONARY['admin'].' FROM '.ClassObject::TABLE_NAME.
                                      ' WHERE '.ClassObject::COLUMN_DICTIONARY['id'].
                                      ' IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = ?)'.
                                      ' UNION '.
                                      'SELECT '.ClassObject::COLUMN_DICTIONARY['name'].','. //Veřejné třídy
                                      ClassObject::COLUMN_DICTIONARY['url'].','.
                                      ClassObject::COLUMN_DICTIONARY['groupsCount'].','.
                                      ClassObject::COLUMN_DICTIONARY['status'].','.
                                      ClassObject::COLUMN_DICTIONARY['admin'].' FROM '.ClassObject::TABLE_NAME.
                                      ' WHERE '.ClassObject::COLUMN_DICTIONARY['status'].' = "public";',
                array(UserManager::getId(), UserManager::getId()), true);
        }
        
        if (!$classes) {
            throw new NoDataException(NoDataException::NO_CLASSES);
        }
        
        //Vytvoř tabulku
        $table = [
            'managed' => [],
            'joined' => [],
            'public' => []
        ];
        foreach ($classes as $dataRow) {
            $tableRow = array();
            $tableRow['rowLink'] = rtrim($_SERVER['REQUEST_URI'], '/').'/'.
                                   $dataRow[ClassObject::COLUMN_DICTIONARY['url']];
           $tableRow[0] = $dataRow[ClassObject::COLUMN_DICTIONARY['name']];
           $tableRow[1] = $dataRow[ClassObject::COLUMN_DICTIONARY['groupsCount']];
            //Tlačítko pro správu třídy, pokud je přihlášený uživatel správcem třídy
            if (UserManager::getId() == $dataRow[ClassObject::COLUMN_DICTIONARY['admin']]) {
                $table['managed'][] = $tableRow;
            } //Tlačítko pro opuštění třídy, pokud není třída veřejná
            else if ($dataRow[ClassObject::COLUMN_DICTIONARY['status']] !== self::CLASS_STATUS_PUBLIC) {
                $table['joined'][] = $tableRow;
            } else {
                $table['public'][] = $tableRow;
            }
        }
        
        return $table;
    }
    
    /**
     * Metoda pro získání seznamu poznávaček v určité třídě a vytvoření tabulky pro předání pohledu
     * Předpokládá se, že již bylo zkontrolováno, zda má přihlášený uživatel přístup do dané třídy
     * @param ClassObject $class Objekt třídy ze které je potřeba získat seznam poznávaček
     * @return array Dvourozměrné pole obsahující seznam poznávaček a další informace potřebné pro pohled
     * @throws NoDataException Pokud ve zvolené poznávačce nejsou žádné části
     * @throws DatabaseException
     */
    public function getGroups(ClassObject $class): array
    {
        //Získej data
        $groups = $class->getGroups();
        if (empty($groups)) {
            throw new NoDataException(NoDataException::NO_GROUPS);
        }
        
        //Vytvoř tabulku
        $table = array();
        foreach ($groups as $group) {
            $tableRow = array();
            $tableRow['rowLink'] = rtrim($_SERVER['REQUEST_URI'], '/').'/'.$group->getUrl();
            $tableRow[0] = $group->getName();
            $tableRow[1] = $group->getPartsCount();
            
            array_push($table, $tableRow);
        }
        
        return $table;
    }
    
    /**
     * Metoda pro získání seznamu částí určité poznávačky v určité třídě a vytvoření tabulky pro předání pohledu
     * Předpokládá se, že již bylo zkontrolováno, zda má přihlášený uživatel přístup do třídy, ve které se nachází daná
     * poznávačka
     * @param Group $group Objekt poznávačky, ze které je potřeba získat seznam částí
     * @return array Dvourozměrné pole obsahující seznam částí a další informace potřebné pro pohled
     * @throws NoDataException Pokud ve zvolené poznávačce nejsou žádné části
     * @throws DatabaseException
     */
    public function getParts(Group $group): array
    {
        //Získej data
        $parts = $group->getParts();
        if (empty($parts)) {
            throw new NoDataException(NoDataException::NO_PARTS);
        }
        
        //Vytvoř tabulku
        $totalNaturals = 0;
        $totalPictures = 0;
        $table = array();
        foreach ($parts as $part) {
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
        if (count($parts) > 1) {
            $tableRow = array();
            $tableRow['rowLink'] = ltrim($_SERVER['REQUEST_URI'], '/');
            $tableRow[0] = 'Vše';
            $tableRow[1] = $totalNaturals;
            $tableRow[2] = $totalPictures;
            
            array_push($table, $tableRow);
        }
        
        return $table;
    }
}

