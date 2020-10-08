<?php

/** 
 * Třída získávající informace o třídě z databáze, například za účelem pro získání ID z názvu a obráceně
 * Dále ověřuje zda třída nebo poznávačka do ní patřící existuje.
 * @author Jan Štěch
 */
class ClassManager
{
    /**
     * Metoda kontrolující, zda třída daného jména existuje
     * @param string $className Jméno třídy
     * @return boolean TRUE, pokud byla třída nalezene, FALSE, pokud ne
     */
    public static function classExists(string $className)
    {
        Db::connect();
        $cnt = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM tridy WHERE '.ClassObject::COLUMN_DICTIONARY['name'].' = ?', array($className), false);
        if ($cnt['cnt'] > 0)
        {
            return true;
        }
        return false;
    }
    
    /**
     * Metoda získávající z databáze seznam všech tříd, které splňují jisté podmínky, jako objekty
     * Podmínky, které musí splňovat:
     * 1) Jejich přístupový kód musí být stejný jako první argument této metody
     * 2) Její status musí být nastaven jako soukromý
     * 3) Uživatel, jehož ID je specifikováno jako druhý argument této metody nesmí být členem daných tříd
     * @param int $code Kód podle kterého vyhledáváme
     * @param int $userId ID uživatele, který se pokouší použít kód k získání přístupu do nových tříd
     * @return ClassObject[]|boolean Pole tříd, které splňují podmínky výše, jako objekty, nebo FALSE, pokud žádné takové třídy neexistují
     */
    public static function getNewClassesByAccessCode(int $code, int $userId)
    {
        Db::connect();
        $result = Db::fetchQuery('
        SELECT
        tridy.'.ClassObject::COLUMN_DICTIONARY['id'].', tridy.'.ClassObject::COLUMN_DICTIONARY['name'].', tridy.'.ClassObject::COLUMN_DICTIONARY['status'].' AS "c_status", tridy.'.ClassObject::COLUMN_DICTIONARY['groupsCount'].', tridy.'.ClassObject::COLUMN_DICTIONARY['code'].',
        uzivatele.'.User::COLUMN_DICTIONARY['id'].', uzivatele.'.User::COLUMN_DICTIONARY['name'].', uzivatele.'.User::COLUMN_DICTIONARY['email'].', uzivatele.'.User::COLUMN_DICTIONARY['lastLogin'].', uzivatele.'.User::COLUMN_DICTIONARY['addedPictures'].', uzivatele.'.User::COLUMN_DICTIONARY['guessedPictures'].', uzivatele.'.User::COLUMN_DICTIONARY['karma'].', uzivatele.'.User::COLUMN_DICTIONARY['status'].' AS "u_status"
        FROM tridy
        JOIN '.User::TABLE_NAME.' ON '.ClassObject::COLUMN_DICTIONARY['admin'].' = '.User::COLUMN_DICTIONARY['id'].'
        WHERE '.ClassObject::COLUMN_DICTIONARY['code'].' = ? AND tridy.'.ClassObject::COLUMN_DICTIONARY['status'].' = ? AND '.ClassObject::COLUMN_DICTIONARY['id'].' NOT IN
        (
            SELECT tridy_id FROM clenstvi WHERE uzivatele_id = ?
        )
        ', array($code, ClassObject::CLASS_STATUS_PRIVATE, $userId), true);
        
        //Kontrola, zda je navrácen alespoň jeden výsledek
        if (!$result)
        {
            return false;
        }
        
        $classes = array();
        foreach($result as $classInfo)
        {
            $classAdmin = new User(false, $classInfo[User::COLUMN_DICTIONARY['id']]);
            $classAdmin->initialize($classInfo[User::COLUMN_DICTIONARY['name']], $classInfo[User::COLUMN_DICTIONARY['email']], new DateTime($classInfo[User::COLUMN_DICTIONARY['lastLogin']]), $classInfo[User::COLUMN_DICTIONARY['addedPictures']], $classInfo[User::COLUMN_DICTIONARY['guessedPictures']], $classInfo[User::COLUMN_DICTIONARY['karma']], $classInfo['u_status']);
            $class = new ClassObject(false, $classInfo[ClassObject::COLUMN_DICTIONARY['id']]);
            $class->initialize($classInfo[ClassObject::COLUMN_DICTIONARY['name']], $classInfo['c_status'], $classInfo[ClassObject::COLUMN_DICTIONARY['code']], null, $classInfo[ClassObject::COLUMN_DICTIONARY['groupsCount']], null, $classAdmin);
            $classes[] = $class;
        }
        
        return $classes;
    }
}