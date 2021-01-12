<?php
namespace Poznavacky\Models\Exceptions;

use \Exception;

/** 
 * Výjimka sloužící pro případ zjištění nedostatečných oprávnění při sestavování webové stránky
 * @author Jan Štěch
 */
class AccessDeniedException extends Exception
{
    const REASON_UNEXPECTED = 'Něco se pokazilo. Opakujte prosím akci později a pokud problém přetrvá, kontaktujte správce';
    const REASON_INSUFFICIENT_PERMISSION = 'Nemáte oprávnění k této akci';
    const REASON_CSRF_TOKEN_INVALID = 'CSRF token nesouhlasí. Aktualizujte prosím stránku. Pokud jste zadávali nějaká data, nejprve si je zkopírujte, jinak budou ztracena.';
    const REASON_USER_NOT_LOGGED_IN = 'Nejste přihlášeni';
    const REASON_NO_PASSWORD_GENERAL = 'Musíte zadat své heslo';
    const REASON_WRONG_PASSWORD_GENERAL = 'Špatné heslo';
    const REASON_LOGIN_NO_NAME = 'Musíte zadat své přihlašovací jméno';
    const REASON_LOGIN_NO_PASSWORD = 'Musíte zadat své heslo';
    const REASON_LOGIN_WRONG_PASSWORD = 'Špatné heslo';
    const REASON_LOGIN_NONEXISTANT_USER = 'Uživatel s tímto jménem neexistuje';
    const REASON_LOGIN_INVALID_COOKIE_CODE = 'Kód pro trvalé přihlášení není platný';
    const REASON_REGISTER_NO_NAME = 'Musíte vyplnit své jméno';
    const REASON_REGISTER_NO_PASSWORD = 'Musíte vyplnit své heslo';
    const REASON_REGISTER_NO_REPEATED_PASSWORD = 'Musíte své heslo vyplnit znovu';
    const REASON_REGISTER_NAME_TOO_SHORT = 'Jméno musí být alespoň 4 znaky dlouhé';
    const REASON_REGISTER_NAME_TOO_LONG = 'Jméno nesmí být více než 15 znaků dlouhé';
    const REASON_REGISTER_PASSWORD_TOO_SHORT = 'Heslo musí být alespoň 6 znaků dlouhé';
    const REASON_REGISTER_PASSWORD_TOO_LONG = 'Heslo nesmí být více než 31 znaků dlouhé';
    const REASON_REGISTER_EMAIL_TOO_LONG = 'E-mail nesmí být delší než 255 znaků';
    const REASON_REGISTER_NAME_INVALID_CHARACTERS = 'Jméno může obsahovat pouze písmena, číslice a mezery';
    const REASON_REGISTER_DUPLICATE_NAME = 'Toto jméno je již používáno jiným uživatelem';
    const REASON_REGISTER_PASSWORD_INVALID_CHARACTERS = 'Vaše heslo obsahuje nepovolený znak.';
    const REASON_REGISTER_DIFFERENT_PASSWORDS = 'Hesla se neshodují';
    const REASON_REGISTER_INVALID_EMAIL = 'E-mail nemá platný formát';
    const REASON_REGISTER_DUPLICATE_EMAIL = 'Tento e-mail již používá jiný uživatel';
    const REASON_PASSWORD_RECOVERY_NO_EMAIL = 'Musíte zadat e-mailovou adresu přidruženou k vašemu účtu';
    const REASON_PASSWORD_RECOVERY_NO_ACCOUNT = 'K této e-mailové adrese není přidružen žádný účet';
    const REASON_RECOVER_NO_TOKEN = 'V adrese není přítomen kód pro obnovení hesla';
    const REASON_RECOVER_INVALID_TOKEN = 'Váš kód pro obnovu hesla je buď neplatný nebo zastaralý';
    const REASON_USER_NOT_MEMBER_IN_CLASS = 'Nemáte přístup do této třídy';
    const REASON_USER_NOT_HAVING_ACCESS_TO_GROUP = 'Nemáte přístup do třídy do které patří tato poznávačka';
    const REASON_CLASS_NOT_CHOSEN = 'Nebyla vybrána žádná třída';
    const REASON_CLASS_NOT_FOUND = 'Tato třída nebyla nalezena';
    const REASON_GROUP_NOT_CHOSEN = 'Nebyla vybrána žádná poznávačka';
    const REASON_GROUP_NOT_FOUND = 'Tato poznávačka nebyla nalezena';
    const REASON_PART_NOT_FOUND = 'Tato část nebyla nalezena';
    const REASON_NATURAL_NOT_FOUND = 'Tato přírodnina nebyla nalezena';
    const REASON_PICTURE_NOT_FOUND = 'Tento obrázek nebyl nalezen';
    const REASON_NEW_CLASS_REQUEST_NO_EMAIL = 'Musíte zadat svůj e-mail, abychom vás mohli kontaktovat';
    const REASON_NEW_CLASS_REQUEST_NO_NAME = 'Musíte zadat název třídy';
    const REASON_NEW_CLASS_REQUEST_NO_CODE = 'Musíte zadat přístupový kód nové třídy';
    const REASON_NEW_CLASS_REQUEST_NO_ANTISPAM = 'Musíte vyplnit ochranu proti robotům';
    const REASON_NEW_CLASS_REQUEST_INVALID_EMAIL = self::REASON_REGISTER_INVALID_EMAIL;
    const REASON_NEW_CLASS_REQUEST_NAME_TOO_SHORT = 'Název třídy musí být alespoň 5 znaků dlouhý';
    const REASON_NEW_CLASS_REQUEST_NAME_TOO_LONG = 'Název třídy nesmí být více než 31 znaků dlouhý';
    const REASON_NEW_CLASS_REQUEST_NAME_INVALID_CHARACTERS = 'Název třídy může obsahovat pouze písmena, číslice, mezeru a znaky . _ -';
    const REASON_NEW_CLASS_REQUEST_DUPLICATE_NAME = 'Třída s tímto nebo velmi podobným názvem již existuje';
    const REASON_NEW_CLASS_REQUEST_FORBIDDEN_URL = 'Toto jméno nelze z technických důvodů použít, pokuste se přidat nebo odebrat některé znaky';
    const REASON_NEW_CLASS_REQUEST_INVALID_CODE = 'Přístupový kód třídy nemá platný formát - musí to být čtyři číslice';
    const REASON_NEW_CLASS_REQUEST_CAPTCHA_FAILED = 'Nepsrávně vyplněná ochrana proti robotům - zkuste to prosím znovu';
    const REASON_NAME_CHANGE_NO_NAME = self::REASON_REGISTER_NO_NAME;
    const REASON_NAME_CHANGE_NAME_TOO_LONG = self::REASON_REGISTER_NAME_TOO_LONG;
    const REASON_NAME_CHANGE_NAME_TOO_SHORT = self::REASON_REGISTER_NAME_TOO_SHORT;
    const REASON_NAME_CHANGE_INVALID_CHARACTERS = self::REASON_REGISTER_NAME_INVALID_CHARACTERS;
    const REASON_NAME_CHANGE_DUPLICATE_NAME = 'Toto jméno již používá jiný uživatel nebo o změnu na něj zažádal';
    const REASON_PASSWORD_CHANGE_NO_OLD_PASSWORD = 'Musíte vyplnit své staré heslo';
    const REASON_PASSWORD_CHANGE_WRONG_PASSWORD = 'Vaše staré heslo je chybné';
    const REASON_PASSWORD_CHANGE_NO_PASSWORD = 'Musíte vyplnit své nové heslo';
    const REASON_PASSWORD_CHANGE_NO_REPEATED_PASSWORD = 'Musíte vyplnit své nové heslo znovu';
    const REASON_PASSWORD_CHANGE_TOO_LONG = self::REASON_REGISTER_PASSWORD_TOO_LONG;
    const REASON_PASSWORD_CHANGE_TOO_SHORT = self::REASON_REGISTER_PASSWORD_TOO_SHORT;
    const REASON_PASSWORD_CHANGE_INVALID_CHARACTERS = self::REASON_REGISTER_PASSWORD_INVALID_CHARACTERS;
    const REASON_PASSWORD_CHANGE_DIFFERENT_PASSWORDS = self::REASON_REGISTER_DIFFERENT_PASSWORDS;
    const REASON_EMAIL_CHANGE_NO_PASSWORD = self::REASON_REGISTER_NO_PASSWORD;
    const REASON_EMAIL_CHANGE_WRONG_PASSWORD = self::REASON_LOGIN_WRONG_PASSWORD;
    const REASON_EMAIL_CHANGE_EMAIL_TOO_LONG = self::REASON_REGISTER_EMAIL_TOO_LONG;
    const REASON_EMAIL_CHANGE_INVALID_EMAIL = self::REASON_REGISTER_INVALID_EMAIL;
    const REASON_EMAIL_CHANGE_DUPLICATE_EMAIL = self::REASON_REGISTER_DUPLICATE_EMAIL;
    const REASON_ACCOUNT_DELETION_NO_PASSWORD = self::REASON_REGISTER_NO_PASSWORD;
    const REASON_ACCOUNT_DELETION_WRONG_PASSWORD = self::REASON_LOGIN_WRONG_PASSWORD;
    const REASON_ACCOUNT_DELETION_CLASS_ADMINISTRATOR = 'Nemůžete odstranit svůj účet, protože spravujete nějakou třídu. Předejte správu tříd, které spravujete, jiným uživatelům pro uvolnění možnosti odstranit svůj účet.';
    const REASON_ADD_PICTURE_UNKNOWN_NATURAL = 'Pokoušíte se přidat obrázek k neznámé přírodnině';
    const REASON_ADD_PICTURE_DUPLICATE_PICTURE = 'Tento obrázek je již k této přírodnině přidán';
    const REASON_ADD_PICTURE_INVALID_FORMAT = 'Zadaná URL adresa nevede na obrázek v platném formátu';
    const REASON_TEST_ANSWER_CHECK_INVALID_QUESTION = 'Neplatné číslo otázky';
    const REASON_REPORT_INVALID_REASON = 'Neplatný důvod';
    const REASON_REPORT_INVALID_ADDITIONAL_INFORMATION = 'Neplatně vyplněné dodatečné informace';
    const REASON_REPORT_UNKNOWN_PICTURE = 'Neznámý obrázek';
    const REASON_MANAGEMENT_NAME_CHANGE_NO_NAME = self::REASON_NEW_CLASS_REQUEST_NO_NAME;
    const REASON_MANAGEMENT_NAME_CHANGE_NAME_TOO_LONG = self::REASON_NEW_CLASS_REQUEST_NAME_TOO_LONG;
    const REASON_MANAGEMENT_NAME_CHANGE_NAME_TOO_SHORT = self::REASON_NEW_CLASS_REQUEST_NAME_TOO_SHORT;
    const REASON_MANAGEMENT_NAME_CHANGE_INVALID_CHARACTERS = self::REASON_NEW_CLASS_REQUEST_NAME_INVALID_CHARACTERS;
    const REASON_MANAGEMENT_NAME_CHANGE_DUPLICATE_NAME = self::REASON_NEW_CLASS_REQUEST_DUPLICATE_NAME;
    const REASON_MANAGEMENT_NAME_CHANGE_FORBIDDEN_URL = self::REASON_NEW_CLASS_REQUEST_FORBIDDEN_URL;
    const REASON_MANAGEMENT_ACCESS_CHANGE_INVALID_CODE = self::REASON_NEW_CLASS_REQUEST_INVALID_CODE;
    const REASON_MANAGEMENT_ACCESS_CHANGE_INVALID_STATUS = 'Zvolený status není platný';
    const REASON_MANAGEMENT_INVITE_USER_UNKNOWN_USER = 'Uživatel nebyl nalezen';
    const REASON_MANAGEMENT_INVITE_USER_PUBLIC_CLASS = 'Pozvánky do veřejné třídy nelze vytvářet';
    const REASON_MANAGEMENT_INVITE_USER_ALREADY_MEMBER = 'Tento uživatel je již členem této třídy';
    const REASON_MANAGEMENT_KICK_USER_PUBLIC_CLASS = 'Z této třídy nelze odebrat žádného člena';
    const REASON_MANAGEMENT_KICK_USER_NOT_A_MEMBER = 'Tento uživatel není členem této třídy';
    const REASON_MANAGEMENT_NEW_GROUP_DUPLICATE_NAME = 'Poznávačka s tímto nebo velmi podobným názvem již ve vaší třídě existuje';
    const REASON_MANAGEMENT_NEW_GROUP_NAME_TOO_LONG = 'Název poznávačky nesmí být více než 31 znaků dlouhý';
    const REASON_MANAGEMENT_NEW_GROUP_NAME_TOO_SHORT = 'Název poznávačky musí být alespoň 3 znaky dlouhý';
    const REASON_MANAGEMENT_NEW_GROUP_NAME_INVALID_CHARACTERS = 'Název poznávačky může obsahovat pouze písmena, číslice, mezeru a znaky . _ -';
    const REASON_MANAGEMENT_REPORTS_EDIT_PICTURE_ANOTHER_GROUP = 'Obrázek nelze přesunout k přírodnině, která není součástí té samé poznávačky';
    const REASON_MANAGEMENT_REPORTS_RESOLVE_PICTURE_FOREIGN_NATURAL = 'Hlášení se vztahuje k obrázku, který není nahrán k žádné přírodnině ve vaší třídě';
    const REASON_MANAGEMENT_EDIT_GROUP_DUPLICATE_GROUP = self::REASON_MANAGEMENT_NEW_GROUP_DUPLICATE_NAME;
    const REASON_MANAGEMENT_EDIT_GROUP_DUPLICATE_PART = 'Jedna nebo více částí se stejným nebo velmi podobným názvem je definována vícekrát'; //Zní to hloupě, ale k dosažení této hlášky je nutné modifikovat JavaScript
    const REASON_MANAGEMENT_EDIT_GROUP_DUPLICATE_NATURAL = 'V jedné nebo více částech se některá přírodnina vyskytuje vícekrát'; //Zní to hloupě, ale k dosažení této hlášky je nutné modifikovat JavaScript
    const REASON_MANAGEMENT_EDIT_GROUP_GROUP_NAME_TOO_LONG = self::REASON_MANAGEMENT_NEW_GROUP_NAME_TOO_LONG;
    const REASON_MANAGEMENT_EDIT_GROUP_GROUP_NAME_TOO_SHORT = self::REASON_MANAGEMENT_NEW_GROUP_NAME_TOO_SHORT;
    const REASON_MANAGEMENT_EDIT_GROUP_GROUP_NAME_INVALID_CHARACTERS = self::REASON_MANAGEMENT_NEW_GROUP_NAME_INVALID_CHARACTERS;
    const REASON_MANAGEMENT_EDIT_GROUP_PART_NAME_TOO_SHORT = 'Název části musí být alespoň 1 znak dlouhý';
    const REASON_MANAGEMENT_EDIT_GROUP_PART_NAME_TOO_LONG = 'Název části nesmí být více než 31 znaků dlouhý';
    const REASON_MANAGEMENT_EDIT_GROUP_PART_NAME_INVALID_CHARACTERS = 'Název části může obsahovat pouze písmena, číslice, mezeru a znaky . _ -';
    const REASON_MANAGEMENT_EDIT_GROUP_NATURAL_NAME_TOO_SHORT = 'Název přírodniny musí být alespoň 1 znak dlouhý';
    const REASON_MANAGEMENT_EDIT_GROUP_NATURAL_NAME_TOO_LONG = 'Název přírodniny nesmí být více než 31 znaků dlouhý';
    const REASON_MANAGEMENT_EDIT_GROUP_NATURAL_NAME_INVALID_CHARACTERS = 'Název přírodniny může obsahovat pouze písmena, číslice, mezeru a znaky . _ - + / * % ( ) \' \"';
    const REASON_ADMINISTRATION_ACCOUNT_UPDATE_INVALID_DATA = 'Jeden nebo více zadaných údajů není platných';
    const REASON_ADMINISTRATION_CLASS_UPDATE_INVALID_DATA = self::REASON_ADMINISTRATION_ACCOUNT_UPDATE_INVALID_DATA;
    const REASON_ADMINISTRATION_ACCOUNT_DELETION_ADMINISTRATOR = 'Tohoto uživatele nemůžete odstranit, protože spravuje některé třídy. Před opakováním akce změňte správce tříd, které tento uživatel spravuje a to skrze záložku "Správa tříd".';
    const REASON_ADMINISTRATION_CLASS_ADMIN_UPDATE_INVALID_IDENTIFIER = 'Neplatný typ změněného identifikátoru';
    const REASON_ADMINISTRATION_CLASS_ADMIN_UPDATE_UNKNOWN_USER = 'Tento uživatel neexistuje';
    const REASON_SEND_EMAIL_INVALID_SENDER_ADDRESS = 'Neplatná adresa odesílatele';
    const REASON_SEND_EMAIL_INVALID_ADDRESSEE_ADDRESS = 'Neplatná adresa adresáta';
    const REASON_SEND_EMAIL_EMPTY_FIELDS = 'S výjimkou patičky e-mailu musí být všechna pole vyplněna';
}

