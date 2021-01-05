<?php
namespace Poznavacky\Controllers;

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
    public function process(array $paremeters): void
    {
        header('HTTP/1.0 403 Forbidden');
        
        $this->pageHeader['title'] = 'Chyba 403';
        $this->pageHeader['description'] = 'Jejda, sem jste se asi nechtěli dostat...';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/error.css');
        $this->pageHeader['jsFiles'] = array();
        $this->pageHeader['bodyId'] = 'error403';
        
        $this->view = 'error403';
    }
}

