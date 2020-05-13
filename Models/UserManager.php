<?php

/** 
 * Třída získávající a nastavující informace o přihlášením uživateli do session
 * @author Jan Štěch
 */
class UserManager
{
    /**
     * Metoda získávající ID aktuálně přihlášeného uživatele
     * @return int ID přihlášeného uživatele
     */
    public static function getId()
    {
        return self::getData('id');
    }
    
    /**
     * Metoda získávající jméno aktuálně přihlášeného uživatele
     * @return string Jméno přihlášeného uživatele
     */
    public static function getName()
    {
        return self::getData('name');
    }
    
    /**
     * Metoda získávající hash hesla aktuálně přihlášeného uživatele
     * @return string Hash hesla přihlášeného uživatele
     */
    public static function getHash()
    {
        return self::getData('hash');
    }
    
    /**
     * Metoda získávající e-mail aktuálně přihlášeného uživatele (pokud jej zadal)
     * @return string|boolean E-mail přihlášeného uživatele nebo FALSE, pokud žádný nezadal
     */
    public static function getEmail()
    {
            $email = self::getData('email');
            if (!empty($email))
            {
                return $email;
            }
            else
            {
                return false;
            }
    }
    
    /**
     * Metoda získávající pole obsahující další informace, konkrétně počet přidaných a uhodnutých obrázků, karmu a status
     * @return array Pole s hodnotami s indexy "addedPictures", "guessedPictures", "karma" a "status"
     */
    public static function getOtherInformation()
    {
        return array('addedPictures' => self::getData('addedPictures'), 'guessedPictures' => self::getData('guessedPictures'), 'karma' => self::getData('karma'), 'status' => self::getData('status'));
    }
    
    /**
     * Metoda získávající konkrétní požadovanou informaci ze $_SESSION
     * @param string $index Klíč, pod kterým je hodnota uložena
     * @throws AccessDeniedException Pokud není uživatel přihlášen
     * @return mixed Hodnota uložená v $_SESSION['user'], pod specifikovaným indexem
     */
    private static function getData(string $index)
    {
        if (AccessChecker::checkUser())
        {
            return $_SESSION['user'][$index];
        }
        else
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_LOGGED_IN, null, null, array('originFile' => 'UserManager.php', 'requestedIndex' => $index));
        }
    }
}