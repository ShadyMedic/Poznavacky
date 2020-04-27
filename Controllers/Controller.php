<?php
/** 
 * Obecný kontroler pro MVC architekturu
 * Mateřská třída všech kontrolerů
 * @author Jan Štěch
 */
abstract class Controller
{
    const ControllerExtension = 'Controller';
    const ModelExtension = 'Model';
    const ControllerFolder = 'Controllers';
    const ModelFolder = 'Models';
    const ViewFolder = 'Views';
    
    protected $controllerToCall;
    protected $data = array();
    protected $view = '';
    protected $pageHeader = array('title' => 'Poznávačky', 'keywords' => '', 'description' => '', 'cssFile' => '', 'jsFile' => '');
    
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
            require self::ViewFolder.'/'.$this->view.'.phtml';
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
    
    /**
     * Metoda konvertující řetězec v kebab-case do CamelCase
     * @param string $str Řetězec ke konverzi
     * @param bool $capitalizeFirst Má být první písmeno velké (default TRUE)
     * @return string Řetězec konvertovaný do CamelCase
     */
    protected function kebabToCamelCase(string $str, bool $capitalizeFirst = true)
    {
        $camel = str_replace('-', ' ', $str);
        $camel = ucwords($camel);
        $camel = str_replace(' ', '', $camel);
        if (!$capitalizeFirst){ $camel = lcfirst($camel); }
        return $camel;
    }
}