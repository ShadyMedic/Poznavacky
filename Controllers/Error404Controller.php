<?php
namespace Poznavacky\Controllers;

/**
 * Kontroler chybové stránky 404
 * @author Jan Štěch
 */
class Error404Controller extends Controller
{

    /**
     * Metoda nastavující hlavičku požadavku, titulek stránky a zobrazovaný pohled chybové stránky
     * @see Controller::process()
     */
    public function process(array $paremeters): void
    {
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

