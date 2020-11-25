<?php
namespace Poznavacky;

//Nastav dependencies pomocí composeru
require __DIR__.'/vendor/autoload.php';

//Definuj a nastav autoloader tříd
function autoloader(string $name): void
{
    if (preg_match('/Controller$/', $name)){ require 'Controllers/'.$name.'.php'; }
    else { require 'Models/'.$name.'.php'; }
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