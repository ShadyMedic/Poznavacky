<?php

/** 
 * Třída získávající a nastavující informace o přihlášením uživateli do session
 * @author Jan Štěch
 */
class UserManager
{
    /**
     * Metoda získávající ID aktuálně přihlášeného uživatele
     * @throws AccessDeniedException Pokud není žádný uživatel přihlášen
     * @return int ID přihlášeného uživatele
     */
    public static function getId()
    {
        if (AccessChecker::checkUser())
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
        if (AccessChecker::checkUser())
        {
            return $_SESSION['user']['name'];
        }
        else
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_LOGGED_IN, null, null, array('originFile' => 'UserManager.php', 'requestedIndex' => 'name'));
        }
    }
    
    /**
     * Metoda získávající e-mail aktuálně přihlášeného uživatele (pokud jej zadal)
     * @throws AccessDeniedException Pokud není žádný uživatel přihlášen
     * @return string|false E-mail přihlášeného uživatele nebo FALSE, pokud žádný nezadal
     */
    public static function getEmail()
    {
        if (AccessChecker::checkUser())
        {
            $email = $_SESSION['user']['email'];
            if (!empty($email))
            {
                return $email;
            }
            else
            {
                return false;
            }
        }
        else
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_LOGGED_IN, null, null, array('originFile' => 'UserManager.php', 'requestedIndex' => 'name'));
        }
    }
}