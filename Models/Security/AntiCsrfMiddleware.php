<?php
namespace Poznavacky\Models\Security;

use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Processors\LoginUser;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use \Exception;

/**
 * Třída obsahující metody pro kontrolu, nastavení a obnovení tokenu sloužícího pro obranu před CSFR útokem
 * @author Jan Štěch
 */
class AntiCsrfMiddleware
{
    public const TOKEN_NAME = 'csfrToken';
    private const TOKEN_LENGTH = 8; //Délka řetězce v bajtech - 8 bajtů --> 16 znaků hexadecimálního kódu
    private const TOKEN_GENERATION_ATTEMPTS = 5; //Počet pokusů na vygenerování náhodného kódu před tím, než je zalogována chyba na úrovni EMERGENCY
    
    /**
     * Metoda kontrolující CSRF token odeslaný s požadavkem jako cookie a porovnává jej s md5() hashem uloženým v
     * $_SESSION Pokud jedna z informací chybí nebo selže ověření, je požadavek zastaven a stane se jedna z
     * následujících akcí Pokud byl požadavek AJAX (detekce nemusí fungovat u starších prohlížečů), je vypsána JSON
     * chybová hláška Pokud požadavek nebyl AJAX, je uživatel přesměrován na domovskou (index) stránku. V obou
     * případech je neplatný token i jeho hash odstraněn z cookie i $_SESSION Pokud ověření proběhne v pořádku, je
     * vygenerován nový token, který ten starý nahradí jak v cookie, tak $_SESSION
     */
    public function verifyRequest(): void
    {
        $accessingPageNotRequiringLogin = (preg_match('/^\/menu/', $_SERVER['REQUEST_URI']) === 0);
        if (!$accessingPageNotRequiringLogin) {
            $userLogged = $this->checkUser();
        } else {
            $userLogged = false;
        }
        $tokenVerified = $this->checkToken();
        
        if (!$accessingPageNotRequiringLogin && !($tokenVerified && $userLogged)) {
            //Token nesouhlasí nebo neexistuje, nebo není přihlášen žádný uživatel a požadavek nevede na index stránku
            
            //Zkontroluj, zda je požadavek AJAX
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                //Požadavek je AJAX --> Vypiš chybovou hlášku
                header('Content-Type: application/json');
                echo json_encode(array(
                    'messageType' => 'error',
                    'message' => AccessDeniedException::REASON_CSRF_TOKEN_INVALID
                ));
            } else {
                //Požadavek není AJAX --> přesměruj na index
                header('Location: /');  //Přesměruj na index stránku
                header('Connection: close');
            }
            $this->destroyToken();  //Odstraň neplatný token z $_COOKIE i $_SESSION
            exit();
        }
        //Vygeneruj a ulož nový token
        $failedCodeGenerationAttempts = 0;
        while ($failedCodeGenerationAttempts < self::TOKEN_GENERATION_ATTEMPTS) {
            try {
                $this->setToken();
                $failedCodeGenerationAttempts = self::TOKEN_GENERATION_ATTEMPTS; //Opusť cyklus
            } catch (Exception $e) {
                $failedCodeGenerationAttempts++;
                if ($failedCodeGenerationAttempts < self::TOKEN_GENERATION_ATTEMPTS) {
                    (new Logger())->error('Při generování nového CSRF tokenu pro uživatele přistupujícího do systému z IP adresy {ip} se vyskytla chyba, zkouším to znovu',
                        array('ip' => $_SERVER['REMOTE_ADDR']));
                } else {
                    (new Logger())->emergency('Pětkrát po sobě se nepodařilo vygenerovat CSRF token pro uživatele přistupujícího do systému z IP adresy {ip}; s největší pravděpodobností je celý systém nepřístupný!',
                        array('ip' => $_SERVER['REMOTE_ADDR']));
                }
            }
        }
    }
    
    /**
     * Metoda kontrolující, zda je token uložený v cookie a jeho otisk uložený v $_SESSION přítomný a zda si odpovídají
     * @return bool TRUE, pokud jsou oba údaje přítomné a odpovídají si (md5 hash tokenu uloženém v cookie odpovídá
     *     hashi uloženému v $_SESSION), FALSE, pokud ne
     */
    private function checkToken(): bool
    {
        if (!isset($_COOKIE[self::TOKEN_NAME]) || !isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }
        $cookieToken = md5($_COOKIE[self::TOKEN_NAME]);
        $sessionToken = $_SESSION[self::TOKEN_NAME];
        
        return ($cookieToken === $sessionToken);
    }
    
    /**
     * Metoda kontrolující, zda je přihlášen nějaký uživatel a případně se pokoušející obnovit jeho přihlášní pomocí
     * kódu pro uchování přihlášení
     * @return bool TRUE, pokud je nějaký uživatel přihlášen, nebo pokud se podařilo obnovit jeho přihlášení, FALSE,
     *     pokud ne
     */
    private function checkUser(): bool
    {
        $aChecker = new AccessChecker();
        if (!$aChecker->checkUser()) {
            //Přihlášení uživatele vypršelo
            //Kontrola instantcookie sezení
            if (isset($_COOKIE['instantLogin'])) {
                try {
                    $userLogger = new LoginUser();
                    $userLogger->processCookieLogin($_COOKIE['instantLogin']);
                    //Přihlášení obnoveno
                    (new Logger())->info('Přihlášení uživatele s ID {userId} přistupujícího do systému z IP adresy {ip} vypršelo, ale bylo obnoveno díky kódu pro okamžité přihlášení (hash {hash})',
                        array(
                            'userId' => UserManager::getId(),
                            'ip' => $_SERVER['REMOTE_ADDR'],
                            'hash' => md5($_COOKIE['instantLogin'])
                        ));
                    return true;
                } catch (AccessDeniedException $e) {
                    //Chybný kód
                    //Vymaž cookie s neplatným kódem
                    $hash = md5($_COOKIE['instantLogin']);
                    setcookie('instantLogin', null, -1, '/');
                    unset($_COOKIE['instantLogin']);
                    (new Logger())->warning('Přihlášení uživatele přistupujícího do systému z IP adresy {ip} vypršelo a kód pro okamžité přihlášení (hash {hash}) nebyl platný a byl proto vymazán',
                        array('ip' => $_SERVER['REMOTE_ADDR'], 'hash' => $hash));
                    return false;
                } catch (DatabaseException $e) {
                    (new Logger())->alert('Přihlášení uživatele přistupujícího do systému z IP adresy {ip} vypršelo a kód pro okamžité přihlášení (hash {hash}) se nepodařilo ověřit kvůli chybě při práci s databází; je možné, že se nelze připojit k databázi a celý systém je tak nefunkční',
                        array('ip' => $_SERVER['REMOTE_ADDR'], 'hash' => md5($_COOKIE['instantLogin'])));
                    return false;
                }
            } else {
                (new Logger())->notice('Přihlášení uživatele přistupujícího do systému z IP adresy {ip} vypršelo',
                    array('ip' => $_SERVER['REMOTE_ADDR']));
                return false;
            }
        }
        return true;
    }
    
    /**
     * Metoda nastavující nový náhodný token do cookie a jeho hash do $_SESSION
     * Nový token nahradí ten stávající
     * @throws Exception Pokud se nepodaří vygenerovat náhodný kód pro CSRF token
     */
    private function setToken(): void
    {
        $code = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $options = array(
            'expires' => 0, //Cookie vyprší na konci sezení
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => false,
            'samesite' => 'Strict'
        );
        setcookie(self::TOKEN_NAME, $code, $options);
        $_SESSION[self::TOKEN_NAME] = md5($code);
    }
    
    /**
     * Metoda odstraňující cookie obsahující token a jeho hash ze $_SESSION
     */
    private function destroyToken(): void
    {
        unset($_SESSION[self::TOKEN_NAME]);
        unset($_COOKIE[self::TOKEN_NAME]);
        setcookie(self::TOKEN_NAME, null, time() - 60, '/');
    }
}

