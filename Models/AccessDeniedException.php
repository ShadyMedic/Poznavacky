<?php

/** 
 * Výjimka sloužící pro případ zjištění nedostatečných oprávnění při sestavování webové stránky
 * @author Jan Štěch
 */
class AccessDeniedException extends Exception
{
    const REASON_USER_NOT_LOGGED_IN = 'Nejste přihlášeni';
    const REASON_LOGIN_WRONG_PASSWORD = 'Špatné heslo';
    const REASON_LOGIN_NONEXISTANT_USER = 'Uživatel s tímto jménem neexistuje';
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