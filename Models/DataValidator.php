<?php

/** 
 * Třída sloužící k ověřování různých dat získaných od uživatele
 * @author Jan Štěch
 */
class DataValidator
{
    /**
     * Metoda ověřující, zda se délka řetězce nachází mezi minimální a maximální hodnotou.
     * @param string $subject Řetězec, jehož délku ověřujeme
     * @param int $min Minimální povolená délka řetězce (včetně)
     * @param int $max Maximální povolená délka řetězce (včetně)
     * @param int $stringType Označení porovnávaného řetězce (pro rozlišení výjimek) - 0 pro jméno, 1 pro heslo, 2 pro e-mail, 3 pro název třídy
     * @throws RangeException Pokud délka řetězce nespadá mezi $min a $max. Zpráva výjimky je 'long' nebo 'short' podle toho, jaká hranice byla přesažena
     * @return boolean TRUE, pokud délka řetězce spadá mezi $min a $max
     */
    public function checkLength($subject, int $min, int $max, int $stringType = null)
    {
        if (mb_strlen($subject) > $max)
        {
            throw new RangeException('long', $stringType);
        }
        if (mb_strlen($subject) < $min)
        {
            throw new RangeException('short', $stringType);
        }
        return true;
    }
    
    /**
     * Metoda ověřující, zda se řetězec skládá pouze z povolených znaků
     * @param string $subject Řetězec, jehož znaky ověřujeme
     * @param string $allowedChars Řetězec skládající se z výčtu všech povolených znaků
     * @param int $stringType Označení porovnávaného řetězce (pro rozlišení výjimek) - 0 pro jméno, 1 pro heslo, 2 pro e-mail (nepoužívané), 3 pro název třídy, 4 pro název poznávačky
     * @throws InvalidArgumentException Pokud se řetězec skládá i z jiných než povolených znaků
     * @returns boolean TRUE, pokud se řetězec skládá pouze z povolených znaků
     */
    public function checkCharacters(string $subject, string $allowedChars, int $stringType = null)
    {
        
        //Není nutné (v tomto případě to ani tak být nesmí) používat mb_strlent
        //strspn totiž nemá multi-byte verzi a pro porovnání délek řetězců se tak musí v obou dvou brát speciální znaky jako více znaků
        //Ukázka: https://pastebin.com/uucr4xEU
        if(strlen($subject) !== strspn($subject, $allowedChars))
        {
            throw new InvalidArgumentException(null, $stringType);
        }
        return true;
    }
    
    /**
     * Metoda ověřující, zda se již řetězec v databázi (v tabulce uzivatele) nevyskytuje
     * @param string $subject Řetězec jehož unikátnost chceme zjistit
     * @param int $stringType Označení porovnávaného řetězce (pro rozlišení výjimek) - 0 pro jméno uživatele, 2 pro e-mail, 3 pro jméno třídy
     * @throws InvalidArgumentException Pokud se již řetězec v databázi vyskytuje
     * @return boolean TRUE, pokud se řetězec zatím v databázi nevyskytuje
     */
    public function checkUniqueness($subject, int $stringType)
    {
        Db::connect();
        switch ($stringType)
        {
            case 0:
                $result = Db::fetchQuery('SELECT SUM(items) AS "cnt" FROM (SELECT COUNT(jmeno) AS "items" FROM '.User::TABLE_NAME.' WHERE jmeno= ? UNION ALL SELECT COUNT(nove) FROM zadosti_jmena_uzivatele WHERE nove= ?) AS tmp', array($subject, $subject), false);
                if ($result['cnt'] > 0)
                {
                    throw new InvalidArgumentException(null, $stringType);
                }
                break;
            case 2:
                if (empty($subject))
                {
                    //Nevyplněný e-mail
                    return true;
                }
                $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM '.User::TABLE_NAME.' WHERE email = ? LIMIT 1', array($subject), false);
                if ($result['cnt'] > 0)
                {
                    throw new InvalidArgumentException(null, $stringType);
                }
                break;
            case 3:
                $result = Db::fetchQuery('SELECT SUM(items) AS "cnt" FROM (SELECT COUNT('.ClassObject::COLUMN_DICTIONARY['name'].') AS "items" FROM '.ClassObject::TABLE_NAME.' WHERE '.ClassObject::COLUMN_DICTIONARY['name'].'= ? UNION ALL SELECT COUNT(nove) FROM zadosti_jmena_tridy WHERE nove= ?) AS tmp', array($subject, $subject), false);
                if ($result['cnt'] > 0)
                {
                    throw new InvalidArgumentException(null, $stringType);
                }
        }
        return true;
    }
    
    /**
     * Metoda získávající ID uživatele přidruženého k e-mailové adrese
     * @param string $email E-mailová adresa, jejíhož vlastníka chceme najít
     * @throws AccessDeniedException Pokud taková adresa nepatří žádnému zaregistrovanému uživateli
     */
    public function getUserIdByEmail(string $email)
    {
        Db::connect();
        $userId = Db::fetchQuery('SELECT uzivatele_id FROM '.User::TABLE_NAME.' WHERE email = ? LIMIT 1', array($email), false);
        if (!$userId)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_RECOVERY_NO_ACCOUNT, null, null, array('originFile' => 'RecoverPassword.php', 'displayOnView' => 'index.phtml', 'form' => 'passRecovery'));
        }
        return $userId['uzivatele_id'];
    }
    
    /**
     * Metoda kontrolující, zda je zadaný kód třídy platný
     * @param string $code Kód zadaný uživatelem
     * @return TRUE, pokud je kód tvořen čtyřmi číslicemi, FALSE, pokud ne
     */
    public function validateClassCode(string $code)
    {
        if (preg_match('/^\d\d\d\d$/', $code)){ return true; }
        return false;
    }
}

