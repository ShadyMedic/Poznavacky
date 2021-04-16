<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\Logger;

/**
 * Kontroler chybové stránky 403
 * @author Jan Štěch
 */
class Error403Controller extends SynchronousController
{

    /**
     * Metoda nastavující hlavičku požadavku, titulek stránky a zobrazovaný pohled chybové stránky
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        (new Logger(true))->notice('Přístup na chybovou stránku 403 z IP adresy {ip}', array('ip' => $_SERVER['REMOTE_ADDR']));

        header('HTTP/1.0 403 Forbidden');
        
        self::$pageHeader['title'] = 'Chyba 403';
        self::$pageHeader['description'] = 'Jejda, sem jste se asi nechtěli dostat...';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/error.css');
        self::$pageHeader['jsFiles'] = array();
        self::$pageHeader['bodyId'] = 'error-403';
    }
}

