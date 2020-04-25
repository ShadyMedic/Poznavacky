<?php
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
    public function process(array $paremeters)
    {
        header('HTTP/1.0 404 Not Found');
        
        $this->pageHeader['title'] = 'Chyba 404';
        $this->pageHeader['cssFile'] = 'css/errors.css';
        
        $this->view = 'error404';
    }
}

