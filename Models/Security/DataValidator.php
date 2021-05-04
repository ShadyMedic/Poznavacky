<?php
namespace Poznavacky\Models\Security;

use Poznavacky\Models\DatabaseItems\ClassNameChangeRequest;
use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\DatabaseItems\Folder;
use Poznavacky\Models\DatabaseItems\Group;
use Poznavacky\Models\DatabaseItems\Natural;
use Poznavacky\Models\DatabaseItems\Part;
use Poznavacky\Models\DatabaseItems\User;
use Poznavacky\Models\DatabaseItems\UserNameChangeRequest;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\Db;
use \BadMethodCallException;
use \InvalidArgumentException;
use \RangeException;

/**
 * Třída sloužící k ověřování různých dat získaných od uživatele
 * @author Jan Štěch
 */
class DataValidator
{
    public const TYPE_USER_NAME = 0;
    public const TYPE_USER_PASSWORD = 1;
    public const TYPE_USER_EMAIL = 2;
    public const TYPE_CLASS_NAME = 3;
    public const TYPE_CLASS_URL = 4;
    public const TYPE_GROUP_NAME = 5;
    public const TYPE_GROUP_URL = 6;
    public const TYPE_PART_NAME = 7;
    public const TYPE_PART_URL = 8;
    public const TYPE_NATURAL_NAME = 9;
    
    public const USER_NAME_MIN_LENGTH = 4;
    public const USER_NAME_MAX_LENGTH = 15;
    public const USER_PASSWORD_MIN_LENGTH = 6;
    public const USER_PASSWORD_MAX_LENGTH = 31;
    public const USER_EMAIL_MIN_LENGTH = 0;
    public const USER_EMAIL_MAX_LENGTH = 255;
    public const CLASS_NAME_MIN_LENGTH = 5;
    public const CLASS_NAME_MAX_LENGTH = 31;
    //Při změně následujících konstant je nutné změnit hodnoty i v souborech edit.js, edit.phtml a naturals.js
    public const GROUP_NAME_MIN_LENGTH = 3;
    public const GROUP_NAME_MAX_LENGTH = 31;
    public const PART_NAME_MIN_LENGTH = 1;
    public const PART_NAME_MAX_LENGTH = 31;
    public const NATURAL_NAME_MIN_LENGTH = 1;
    public const NATURAL_NAME_MAX_LENGTH = 31;
    
    public const USER_NAME_ALLOWED_CHARS = '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ ';
    public const USER_PASSWORD_ALLOWED_CHARS = '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ {}()[]#:;^,.?!|_`~@$%/+-*=\"\'';
    public const USER_EMAIL_ALLOWED_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@.!#$%&\'*+-/=?^_`{|}~';  //Inspirováno https://stackoverflow.com/a/2049510/14011077
    public const CLASS_NAME_ALLOWED_CHARS = '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-';
    public const GROUP_NAME_ALLOWED_CHARS = '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-';
    public const PART_NAME_ALLOWED_CHARS = '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-';  //Při změně je nutné změnit hodnoty i v souboru edit.js
    public const NATURAL_NAME_ALLOWED_CHARS = '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ _.-+/*%()\'\"';  //Při změně je nutné změnit hodnoty i v souboru edit.js
    
    public const URL_ALLOWED_CHARS = '0123456789abcdefghijklmnopqrstuvwxyz'; //(plus znak -)
    
    public const RESERVED_URLS = array(
        "error403",
        "error404",
        "index",
        "index-forms",
        "legal",
        "recover-password",
        "token-password-change",
        "administrate",
        "administrate-action",
        "logout",
        "leave",
        "invitation",
        "enter-class-code",
        "request-new-class",
        "menu",
        "add-pictures",
        "submit-picture",
        "learn",
        "learn-pictures",
        "test",
        "test-pictures",
        "check-test-answer",
        "new-report",
        "manage",
        "class-update",
        "members",
        "naturals",
        "update-naturals",
        "tests",
        "edit",
        "confirm-group-edit",
        "reports",
        "report-action",
        "account-settings",
        "account-update"
    );
    
    /**
     * Metoda ověřující, zda se délka řetězce nachází mezi minimální a maximální hodnotou.
     * Všechny parametry (kromě prvního) by měly nabývat hodnoty jedné z konstant této třídy.
     * @param string $subject Řetězec, jehož délku ověřujeme
     * @param int $min Minimální povolená délka řetězce (včetně)
     * @param int $max Maximální povolená délka řetězce (včetně)
     * @param int $stringType Označení porovnávaného řetězce (pro rozlišení výjimek) - viz konstanty této třídy
     *     začínající na "TYPE_"
     * @return boolean TRUE, pokud délka řetězce spadá mezi $min a $max
     * @throws RangeException Pokud délka řetězce nespadá mezi $min a $max. Zpráva výjimky je 'long' nebo 'short' podle
     *     toho, jaká hranice byla přesažena
     */
    public function checkLength(string $subject, int $min, int $max, int $stringType): bool
    {
        if (mb_strlen($subject) > $max) {
            throw new RangeException('long', $stringType);
        }
        if (mb_strlen($subject) < $min) {
            throw new RangeException('short', $stringType);
        }
        return true;
    }
    
    /**
     * Metoda ověřující, zda se řetězec skládá pouze z povolených znaků
     * Všechny parametry (kromě prvního) by měly nabývat hodnoty jedné z konstant této třídy.
     * @param string $subject Řetězec, jehož znaky ověřujeme
     * @param string $allowedChars Řetězec skládající se z výčtu všech povolených znaků
     * @param int $stringType Označení porovnávaného řetězce (pro rozlišení výjimek) - viz konstanty této třídy
     *     začínající na "TYPE_"
     * @return boolean TRUE, pokud se řetězec skládá pouze z povolených znaků
     * @throws InvalidArgumentException Pokud se řetězec skládá i z jiných než povolených znaků
     */
    public function checkCharacters(string $subject, string $allowedChars, int $stringType): bool
    {
        
        //Není nutné (v tomto případě to ani tak být nesmí) používat mb_strlent
        //strspn totiž nemá multi-byte verzi a pro porovnání délek řetězců se tak musí v obou dvou brát speciální znaky jako více znaků
        //Ukázka: https://pastebin.com/uucr4xEU
        if (strlen($subject) !== strspn($subject, $allowedChars)) {
            throw new InvalidArgumentException(null, $stringType);
        }
        return true;
    }
    
    /**
     * Metoda ověřující, zda se již řetězec v adekvátní databázové tabulce nevyskytuje
     * Takto lze kontrolovat pouze uživatelské jméno, jméno třídy nebo uživatelský e-mail
     * Při kontrole uživatelského jména není brán ohled na velikost písmen
     * @param string $subject Řetězec jehož unikátnost chceme zjistit
     * @param int $stringType Označení porovnávaného řetězce (pro rozlišení výjimek) - viz konstanty této třídy
     *     začínající na "TYPE_"
     * @param Folder|null $parentFolder Objekt složky, jejíž součástí je kontrolovaný objekt (pouze u poznávaček a
     *     jejich částí) - bude tak kontrolována unikátnout pouze vůči položkám ve stejné složce (u ostatních typů
     *     nepovinné)
     * @return boolean TRUE, pokud se řetězec zatím v databázi nevyskytuje
     * @throws BadMethodCallException Pokud druhý argument neoznačuje položku, pro kterou je tato operace podporována
     *     nebo pokud není vyplněn poslední argument a je kontrolována unikátnost URL poznávačky nebo části
     * @throws InvalidArgumentException Pokud se již řetězec v databázi vyskytuje
     * @throws DatabaseException
     */
    public function checkUniqueness(string $subject, int $stringType, Folder $parentFolder = null): bool
    {
        switch ($stringType) {
            case self::TYPE_USER_NAME:
                $result = Db::fetchQuery('SELECT SUM(items) AS "cnt" FROM (SELECT COUNT('.
                                         User::COLUMN_DICTIONARY['name'].') AS "items" FROM '.User::TABLE_NAME.
                                         ' WHERE UPPER('.User::COLUMN_DICTIONARY['name'].
                                         ') = ? UNION ALL SELECT COUNT('.
                                         UserNameChangeRequest::COLUMN_DICTIONARY['newName'].') FROM '.
                                         UserNameChangeRequest::TABLE_NAME.' WHERE UPPER('.
                                         UserNameChangeRequest::COLUMN_DICTIONARY['newName'].') = ?) AS tmp',
                    array(strtoupper($subject), strtoupper($subject)), false);
                if ($result['cnt'] > 0) {
                    throw new InvalidArgumentException(null, $stringType);
                }
                break;
            case self::TYPE_USER_EMAIL:
                if (empty($subject)) {
                    //Nevyplněný e-mail
                    return true;
                }
                $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM '.User::TABLE_NAME.' WHERE '.
                                         User::COLUMN_DICTIONARY['email'].' = ? LIMIT 1', array($subject), false);
                if ($result['cnt'] > 0) {
                    throw new InvalidArgumentException(null, $stringType);
                }
                break;
            case self::TYPE_CLASS_URL:
                $result = Db::fetchQuery('SELECT SUM(items) AS "cnt" FROM (SELECT COUNT('.
                                         ClassObject::COLUMN_DICTIONARY['url'].') AS "items" FROM '.
                                         ClassObject::TABLE_NAME.' WHERE '.ClassObject::COLUMN_DICTIONARY['url'].
                                         '= ? UNION ALL SELECT COUNT('.
                                         ClassNameChangeRequest::COLUMN_DICTIONARY['newUrl'].') FROM '.
                                         ClassNameChangeRequest::TABLE_NAME.' WHERE '.
                                         ClassNameChangeRequest::COLUMN_DICTIONARY['newUrl'].'= ?) AS tmp',
                    array($subject, $subject), false);
                if ($result['cnt'] > 0) {
                    throw new InvalidArgumentException(null, $stringType);
                }
                break;
            case self::TYPE_GROUP_URL:
                if ($parentFolder === null) {
                    throw new BadMethodCallException('Parent object must be specified for this check');
                }
                
                $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM '.Group::TABLE_NAME.' WHERE '.
                                         Group::COLUMN_DICTIONARY['url'].' = ? AND '.Group::COLUMN_DICTIONARY['class'].
                                         ' = ? LIMIT 1', array($subject, $parentFolder->getId()), false);
                if ($result['cnt'] > 0) {
                    throw new InvalidArgumentException(null, $stringType);
                }
                break;
            case self::TYPE_PART_URL:
                if ($parentFolder === null) {
                    throw new BadMethodCallException('Parent object must be specified for this check');
                }
                
                $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM '.Part::TABLE_NAME.' WHERE '.
                                         Part::COLUMN_DICTIONARY['url'].' = ? AND '.Part::COLUMN_DICTIONARY['group'].
                                         ' = ? LIMIT 1', array($subject, $parentFolder->getId()), false);
                if ($result['cnt'] > 0) {
                    throw new InvalidArgumentException(null, $stringType);
                }
                break;
            case self::TYPE_NATURAL_NAME:
                if ($parentFolder === null) {
                    throw new BadMethodCallException('Parent object must be specified for this check');
                }
                
                $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM '.Natural::TABLE_NAME.' WHERE '.
                                         Natural::COLUMN_DICTIONARY['name'].' = ? AND '.
                                         Natural::COLUMN_DICTIONARY['class'].' = ? LIMIT 1',
                    array($subject, $parentFolder->getId()), false);
                if ($result['cnt'] > 0) {
                    throw new InvalidArgumentException(null, $stringType);
                }
                break;
            default:
                throw new BadMethodCallException('Invalid string type');
        }
        return true;
    }
    
    /**
     * Metoda ověřující, zda dané URL není shodné s URL používaným pro přístup k některému kontroleru
     * @param string $subject URL třídy, poznávačky nebo části ke kontrole
     * @param int $stringType Označení kontrolovaného řetězce (pro rozlišení výjimek a výber seznamu pravidel) - viz
     *     konstanty této třídy začínající na "TYPE_"
     * @return bool TRUE, pokud je možné URL použít
     * @throws BadMethodCallException Pokud druhý argument neoznačuje položku, pro kterou je tato operace podporována
     * @throws InvalidArgumentException Pokud je daná URL rezervovaná pro přístup k některému kontroleru
     */
    public function checkForbiddenUrls(string $subject, int $stringType): bool
    {
        if (in_array($subject, self::RESERVED_URLS)) {
            throw new InvalidArgumentException(null, $stringType);
        }
        return true;
    }
    
    /**
     * Metoda získávající ID uživatele přidruženého k e-mailové adrese
     * @param string $email E-mailová adresa, jejíhož vlastníka chceme najít
     * @return int ID uživatele, kterému patří daná e-mailová adresa
     * @throws DatabaseException
     * @throws AccessDeniedException Pokud taková adresa nepatří žádnému zaregistrovanému uživateli
     */
    public function getUserIdByEmail(string $email): int
    {
        $userId = Db::fetchQuery('SELECT '.User::COLUMN_DICTIONARY['id'].' FROM '.User::TABLE_NAME.' WHERE '.
                                 User::COLUMN_DICTIONARY['email'].' = ? LIMIT 1', array($email), false);
        if (!$userId) {
            throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_RECOVERY_NO_ACCOUNT, null, null);
        }
        return $userId[User::COLUMN_DICTIONARY['id']];
    }
    
    /**
     * Metoda kontrolující, zda je zadaný kód třídy platný
     * @param string $code Kód zadaný uživatelem
     * @return boolean TRUE, pokud je kód tvořen čtyřmi číslicemi, FALSE, pokud ne
     */
    public function validateClassCode(string $code): bool
    {
        if (preg_match('/^\d\d\d\d$/', $code)) {
            return true;
        }
        return false;
    }
}

