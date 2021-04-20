<?php
namespace Poznavacky\Models\Statics;

use Poznavacky\Models\DatabaseItems\LoggedUser;
use Poznavacky\Models\DatabaseItems\User;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Security\AccessChecker;

/**
 * Třída získávající a nastavující informace o přihlášením uživateli do session
 * @author Jan Štěch
 */
class UserManager
{
    /**
     * Metoda navracející objekt s vlastnostmi přihlášeného uživatele uložený v $_SESSION
     * @return LoggedUser Objekt obsahující data přihlášeného uživatele
     * @throws AccessDeniedException Pokud není žádný uživatel přihlášen
     */
    public static function getUser(): User
    {
        $aChecker = new AccessChecker();
        if ($aChecker->checkUser()) {
            return $_SESSION['user'];
        } else {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_LOGGED_IN, null, null);
        }
    }
    
    /**
     * Metoda získávající ID aktuálně přihlášeného uživatele
     * @return int ID přihlášeného uživatele
     * @throws AccessDeniedException Pokud není žádný uživatel přihlášen
     */
    public static function getId(): int
    {
        return self::getData('id');
    }
    
    /**
     * Metoda získávající jméno aktuálně přihlášeného uživatele
     * @return string Jméno přihlášeného uživatele
     * @throws AccessDeniedException Pokud není žádný uživatel přihlášen
     */
    public static function getName(): string
    {
        return self::getData('name');
    }
    
    /**
     * Metoda získávající hash hesla aktuálně přihlášeného uživatele
     * @return string Hash hesla přihlášeného uživatele
     * @throws AccessDeniedException Pokud není žádný uživatel přihlášen
     */
    public static function getHash(): string
    {
        return self::getData('hash');
    }
    
    /**
     * Metoda získávající e-mail aktuálně přihlášeného uživatele (pokud jej zadal)
     * @return string|boolean E-mail přihlášeného uživatele nebo FALSE, pokud žádný nezadal
     * @throws AccessDeniedException Pokud není žádný uživatel přihlášen
     */
    public static function getEmail(): string
    {
        $email = self::getData('email');
        if (!empty($email)) {
            return $email;
        } else {
            return false;
        }
    }
    
    /**
     * Metoda získávající pole obsahující další informace, konkrétně počet přidaných a uhodnutých obrázků, URL adresu
     * poslední zobrazené menu tabulky, karmu, a status
     * @return array Pole s hodnotami s indexy "addedPictures", "guessedPictures", "lastMenuTableUrl", "karma" a
     *     "status"
     * @throws AccessDeniedException Pokud není žádný uživatel přihlášen
     */
    public static function getOtherInformation(): array
    {
        return array(
            'addedPictures' => self::getData('addedPictures'),
            'guessedPictures' => self::getData('guessedPictures'),
            'lastMenuTableUrl' => self::getData('lastMenuTableUrl'),
            'karma' => self::getData('karma'),
            'status' => self::getData('status')
        );
    }
    
    /**
     * Metoda získávající konkrétní požadovanou informaci ze $_SESSION
     * @param string $index Klíč, pod kterým je hodnota uložena
     * @return mixed Hodnota uložená v $_SESSION['user'], pod specifikovaným indexem
     * @throws AccessDeniedException Pokud není žádný uživatel přihlášen
     */
    private static function getData(string $index)
    {
        $aChecker = new AccessChecker();
        if ($aChecker->checkUser()) {
            return $_SESSION['user'][$index];
        } else {
            throw new AccessDeniedException(AccessDeniedException::REASON_USER_NOT_LOGGED_IN, null, null);
        }
    }
}

