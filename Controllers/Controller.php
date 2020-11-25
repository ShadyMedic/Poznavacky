<?php
namespace Poznavacky\Controllers;

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
    protected $pageHeader = array('title' => 'Poznávačky', 'keywords' => '', 'description' => '', 'cssFile' => array(), 'jsFile' => array());
    
    /**
     * Metoda zpracovávající parametry z URL adresy
     * @param array $parameters Paremetry ke zpracování jako pole
     */
    abstract function process(array $parameters): void;
    
    /**
     * Metoda zahrnující pohled a vypysující do něj proměnné z vlastnosti $data
     */
    public function displayView(): void
    {
        if ($this->view)
        {
            //Vytvoř pole ošetřených hodnot
            $sanitized = $this->sanitizeData($this->data);
            
            //Přejmenuj klíče v originálním poli neošetřených hodnot
            foreach ($this->data as $key => $value)
            {
                $this->data['_'.$key.'_'] = $value;
                unset($this->data[$key]);
            }
            
            extract($this->data);
            extract($sanitized);
            require self::ViewFolder.'/'.$this->view.'.phtml';
        }
    }
    
    /**
     * Metoda ošetřující všechny hodnoty určené k využití pohledem proti XSS útoku
     * @param array $data Pole proměnných k ošetření
     * @return mixed Pole s ošetřenými hodnotami
     */
    private function sanitizeData(array $data): array
    {
        $sanitizer = new AntiXssSanitizer();
        foreach ($data as $key => $value)
        {
            $data[$key] = $sanitizer->sanitize($value);
        }
        return $data;
    }
    
    /**
     * Metoda přesměrovávající uživatele na jinou adresu a ukončující běh skriptu
     * @param string $url
     */
    public function redirect(string $url): void
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
    protected function kebabToCamelCase(string $str, bool $capitalizeFirst = true): string
    {
        $camel = str_replace('-', ' ', $str);
        $camel = ucwords($camel);
        $camel = str_replace(' ', '', $camel);
        if (!$capitalizeFirst){ $camel = lcfirst($camel); }
        return $camel;
    }
    
    /**
     * Metoda přidávající do sezení nový objekt s hláškou pro uživatele pro zobrazení na příští načtené stránce
     * @param int $type
     * @param string $msg
     */
    protected function addMessage(int $type, string $msg): void
    {
        $messageBox = new MessageBox($type, $msg);
        if (isset($_SESSION['messages']))
        {
            $_SESSION['messages'][] = $messageBox;
        }
        else
        {
            $_SESSION['messages'] = array($messageBox);
        }
    }
}

