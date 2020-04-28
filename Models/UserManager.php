<?php

/** 
 * Třída získávající a nastavující informace o přihlášením uživateli do session
 * @author Jan Štěch
 */
class UserManager
{
    /**
     * Metoda kontrolující existenci sezení a popřípadě zakládající nové
     */
    private static function checkSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
        {
            session_start();
        }
    }
    
    /**
     * Metoda získávající ID aktuálně přihlášeného uživatele
     * @throws AccessDeniedException Pokud není žádný uživatel přihlášen
     * @return int ID přihlášeného uživatele
     */
    public static function getId()
    {
        self::checkSession();
        if (isset($_SESSION['user']))
        {
            return $_SESSION['user']['id'];
        }
        else
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_LOGGED_IN, null, null, array('originFile' => 'UserManager.php', 'requestedIndex' => 'id'));
        }
    }
    
    /**
     * Metoda získávající jméno aktuálně přihlášeného uživatele
     * @throws AccessDeniedException Pokud není žádný uživatel přihlášen
     * @return string Jméno přihlášeného uživatele
     */
    public static function getName()
    {
        self::checkSession();
        if (isset($_SESSION['user']))
        {
            return $_SESSION['user']['name'];
        }
        else
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_LOGGED_IN, null, null, array('originFile' => 'UserManager.php', 'requestedIndex' => 'name'));
        }
    }
}