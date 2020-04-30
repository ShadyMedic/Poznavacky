<?php

/** 
 * Výjimka sloužící pro případ zjištění nedostatečných oprávnění při sestavování webové stránky
 * @author Jan Štěch
 */
class AccessDeniedException extends Exception
{
    const REASON_USER_NOT_LOGGED_IN = 'Nejste přihlášeni';
    const REASON_LOGIN_NO_NAME = 'Musíte zadat své přihlašovací jméno';
    const REASON_LOGIN_NO_PASSWORD = 'Musíte zadat své heslo';
    const REASON_LOGIN_WRONG_PASSWORD = 'Špatné heslo';
    const REASON_LOGIN_NONEXISTANT_USER = 'Uživatel s tímto jménem neexistuje';
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
    const REASON_USER_NOT_MEMBER_IN_CLASS = 'Nemáte přístup do této třídy';
    const REASON_USER_NOT_HAVING_ACCESS_TO_GROUP = 'Nemáte přístup do třídy do které patří tato poznávačka';
    const REASON_CLASS_NOT_CHOSEN = 'Nebyla vybrána žádná třída';
    
    private $additionalInfo = array();
    
    /**
     * Konstruktor přístupové podmínky
     * @param string $message Obecná zpráva, která může být zobrazena běžnému uživateli
     * @param int $code Číslo chyby, které může být zobrazeno běžnému uživateli
     * @param Exception $previous Předcházející podmínka (pro účely propagace podmínek)
     */
    public function __construct(string $message = null, $code = null, $previous = null, array $additionalInfo = null)
    {
        parent::__construct($message, $code, $previous);
        $this->additionalInfo = $additionalInfo;
    }
    
    /**
     * Funkce navracející určitý prvek z pole s přídavnými informacemi
     * @param mixed $subject Klíč prvku pro získání
     */
    public function getAdditionalInfo($subject)
    {
        if (isset($this->additionalInfo[$subject]))
        {
            return $this->additionalInfo[$subject];
        }
        else
        {
            throw new OutOfBoundsException('Invalid array offset in the AccessDeniedException: '.$subject);
        }
    }
}