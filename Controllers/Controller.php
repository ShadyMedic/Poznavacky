<?php
namespace Poznavacky;

/** 
 * Obecný kontroler pro MVC architekturu
 * Mateřská třída všech kontrolerů
 * @author Jan Štěch
 */
abstract class Controller
{
    protected $data = array();
    protected $view = '';
    protected $pageHeader = array('title' => 'Poznávačky', 'keywords' => '', description => '');
    
    /**
     * Funkce zpracovávající parametry z URL adresy
     * @param array $paremeters Paremetry ke zpracování jako pole
     */
    abstract function process(array $paremeters);
    
    /**
     * Funkce zahrnující pohled a vypysující do něj proměnné z vlastnosti $data
     */
    public function displayView()
    {
        if ($this->view)
        {
            extract($this->data);
            require 'Views/'.$this->view.'.phtml';
        }
    }
    /**
     * Funkce přesměrovávající uživatele na jinou adresu a ukončující běh skriptu
     * @param string $url
     */
    public function redirect(string $url)
    {
        header('Location: /'.$url);
        header('Connection: close');
        exit();
    }
}