<?php

/** 
 * Třída získávající a nastavující informace o přihlášením uživateli do session
 * @author Jan Štěch
 */
class UserManager
{
    private static function checkSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
        {
            session_start();
        }
    }
    
    public static function getId()
    {
        self::checkSession();
        if (isset($_SESSION['user']))
        {
            return $_SESSION['user']['id'];
        }
        else
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_LOGGED_IN, null, null, array('originFile' => 'UserManager.php', 'displayOnView' => 'menu.phtml', 'requestedIndex' => 'id'));
        }
    }
    
    public static function getName()
    {
        self::checkSession();
        if (isset($_SESSION['user']))
        {
            return $_SESSION['user']['name'];
        }
        else
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_LOGGED_IN, null, null, array('originFile' => 'UserManager.php', 'displayOnView' => 'menu.phtml', 'requestedIndex' => 'name'));
        }
    }
}