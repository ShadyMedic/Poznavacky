<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\Logger;

/**
 * Kontroler chybové stránky 403
 * @author Jan Štěch
 */
class Error403Controller extends Controller
{

    /**
     * Metoda nastavující hlavičku požadavku, titulek stránky a zobrazovaný pohled chybové stránky
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        (new Logger(true))->notice('Přístup na chybovou stránku 403 z IP adresy {ip}', array('ip' => $_SERVER['REMOTE_ADDR']));

        header('HTTP/1.0 403 Forbidden');
        
        $this->pageHeader['title'] = 'Chyba 403';
        $this->pageHeader['description'] = 'Jejda, sem jste se asi nechtěli dostat...';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/error.css');
        $this->pageHeader['jsFiles'] = array();
        $this->pageHeader['bodyId'] = 'error-403';
        
        $this->view = 'error403';
    }
}

