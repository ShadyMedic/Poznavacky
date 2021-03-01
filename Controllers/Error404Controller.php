<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\Logger;

/**
 * Kontroler chybové stránky 404
 * @author Jan Štěch
 */
class Error404Controller extends SynchronousController
{

    /**
     * Metoda nastavující hlavičku požadavku, titulek stránky a zobrazovaný pohled chybové stránky
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        (new Logger(true))->notice('Přístup na chybovou stránku 404 z IP adresy {ip}', array('ip' => $_SERVER['REMOTE_ADDR']));

        header('HTTP/1.0 404 Not Found');
        
        $this->pageHeader['title'] = 'Chyba 404';
        $this->pageHeader['description'] = 'Jejda, sem jste se asi nechtěli dostat...';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/error.css');
        $this->pageHeader['jsFiles'] = array();
        $this->pageHeader['bodyId'] = 'error-404';
        
        $this->view = 'error404';
    }
}

