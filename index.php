<?php
namespace Poznavacky;

use Poznavacky\Models\Security\AntiCsrfMiddleware;
use Poznavacky\Models\Statics\Settings;
use Poznavacky\Models\Logger;
use Poznavacky\Controllers\RooterController;
use \ErrorException;

//Nastav dependencies pomocí composeru
require __DIR__.'/vendor/autoload.php';

//Definuj a nastav autoloader tříd
function autoloader(string $name): void
{
    //Nahraď zpětná lomítka (používaných v namespacové cestě) běznými lomítky (používaných pro navigaci adresáři)
    $name = str_replace('\\', '/', $name);
    //Odstraň z cesty ke třídě kořenovou složku (v té už je tento soubor)
    if (strpos($name, '/') !== false) {
        $folders = explode('/', $name);
        unset($folders[0]);
        $name = implode('/', $folders);
    }
    $name .= '.php';
    require $name;
}

spl_autoload_register('Poznavacky\\autoloader');

//Obnov session a nastav kódování
session_start();
mb_internal_encoding('UTF-8');

//Zkontroluj, zda je navázáno zabezpečné připojení a případně přesměruj
if (Settings::PRODUCTION_ENVIRONMENT) {
    if (!(isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] === "https")) {
        (new Logger(true))->notice('Uživatel se pokusil odeslat požadavek na adresu {uri} z IP adresy {ip}, avšak nepoužil zabezpečené SSL připojení',
            array('uri' => $_SERVER['REQUEST_URI'], 'ip' => $_SERVER['REMOTE_ADDR']));
        header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        header('Connection: close');
        exit();
    }
}

//Zkontroluj a obnov CSRF token (toto také přesměruje nepřihlášené uživatele pokoušející se přistoupit na nějakou menu stránku na index)
$antiCSRF = new AntiCsrfMiddleware();
$antiCSRF->verifyRequest(); //V případě chyby (včetně vypršení sezení) je na tomto řádku skript zastaven

//Inicializuj dočasné úložiště
$_SESSION['temp'] = array();

//Zpracuj URL adresu a získej data pro zobrazení
$rooter = new RooterController();
$rooter->process(array($_SERVER['REQUEST_URI']));

//Vymaž dočasné úložiště
unset($_SESSION['temp']);

//Zobraz vygenerovanou webovou stránku
try {
    $rooter->displayView();
} catch (ErrorException $e) {
    (new Logger(true))->emergency('Uživatel odeslal požadavek na URL adresu {url} z IP adresy {ip}, avšak stránka mu nemohla být zobrazena kvůli selhání ošetření proti XSS útoku',
        array('url' => $_SERVER['REQUEST_URI'], 'ip' => $_SERVER['REMOTE_ADDR']));
    echo "Systém má aktuálně nějaké potíže. Kontaktujte prosím webmastera a zkuste akci opakovat později.";
}

