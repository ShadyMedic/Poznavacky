<?php
namespace Poznavacky;

//Nastav dependencies pomocí composeru
require __DIR__.'/vendor/autoload.php';

//Definuj a nastav autoloader tříd
function autoloader(string $name): void
{
    //Nahraď zpětná lomítka (používaných v namespacové cestě) běznými lomítky (používaných pro navigaci adresáři)
    $name = str_replace('\\', '/', $name);
    //Odstraň z cesty ke třídě kořenovou složku (v té už je tento soubor)
    $folders = explode('/', $name);
    unset($folders[0]);
    $name = implode('/', $folders);
    
    require $name.'.php';
}
spl_autoload_register('autoloader');

//Obnov session a nastav kódování
session_start();
mb_internal_encoding('UTF-8');

//Zkontroluj a obnov CSRF token
$antiCSRF = new AntiCsrfMiddleware();
$antiCSRF->verifyRequest(); //V případě chyby je na tomto řádku skript zastaven

//Zpracuj URL adresu a zobraz vygenerovanou webovou stránku
$rooter = new RooterController();
$rooter->process(array($_SERVER['REQUEST_URI']));
$rooter->displayView();