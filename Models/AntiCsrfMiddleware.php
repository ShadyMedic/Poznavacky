<?php
/**
 * Třída obsahující metody pro kontrolu, nastavení a obnovení tokenu sloužícího pro obranu před CSFR útokem
 * @author Jan Štěch
 */
class AntiCsrfMiddleware
{
    private const TOKEN_NAME = 'csfrToken';
    private const TOKEN_LENGTH = 8; //Délka řetězce v bajtech - 8 bajtů --> 16 znaků hexadecimálního kódu
    
    /**
     * Metoda kontrolující CSRF token odeslaný s požadavkem jako cookie a porovnává jej s md5() hashem uloženým v $_SESSION
     * Pokud jedna z informací chybí nebo selže ověření, je požadavek zastaven a stane se jedna z následujících akcí
     * Pokud byl požadavek AJAX (detekce nemusí fungovat u starších prohlížečů), je vypsána JSON chybová hláška
     * Pokud požadavek nebyl AJAX, je uživatel přesměrován na domovskou (index) stránku.
     * V obou případech je neplatný token i jeho hash odstraněn z cookie i $_SESSION
     * Pokud ověření proběhne v pořádku, je vygenerován nový token, který ten starý nahradí jak v cookie, tak $_SESSION
     */
    public function verifyRequest(): void
    {
        if (!$this->checkToken() && !($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index'))
        {
            //Token nesouhlasí nebo neexistuje a požadavek nevede na index stránku
            
            //Zkontroluj, zda je požadavek AJAX
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' )
            {
                //Požadavek je AJAX --> Vypiš chybovou hlášku
                header('Content-Type: application/json');
                echo json_encode(array('messageType' => 'error', 'message' => AccessDeniedException::REASON_CSRF_TOKEN_INVALID));
            }
            else
            {
                //Požadavek není AJAX --> přesměruj na index
                header('Location: /');  //Přesměruj na index stránku
                header('Connection: close');
            }
            $this->destroyToken();  //Odstraň neplatný token z $_COOKIE i $_SESSION
            exit();
        }
        $this->setToken(); //Vygeneruj a ulož nový token
    }
    
    /**
     * Metoda kontrolující, zda je token uložený v cookie a jeho otisk uložený v $_SESSION přítomný a zda si odpovídají
     * @return bool TRUE, pokud jsou oba údaje přítomné a odpovídají si (md5 hash tokenu uloženém v cookie odpovídá hashi uloženému v $_SESSION), FALSE, pokud ne
     */
    private function checkToken(): bool
    {
        if (!isset($_COOKIE[self::TOKEN_NAME]) || !isset($_SESSION[self::TOKEN_NAME])) { return false; }
        $cookieToken = md5($_COOKIE[self::TOKEN_NAME]);
        $sessionToken = $_SESSION[self::TOKEN_NAME];
        
        return ($cookieToken === $sessionToken);
    }
    
    /**
     * Metoda nastavující nový náhodný token do cookie a jeho hash do $_SESSION
     * Nový token nahradí ten stávající
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

