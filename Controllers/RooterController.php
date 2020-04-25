<?php
/**
 * Třída směrovače přesměrovávající uživatele z index.php na správný kontroler
 * @author Jan Štěch       
 */
class RooterController extends Controller
{
    protected $controllerToCall;
    
    /**
     * Metoda zpracovávající zadanou URL adresu a přesměrovávající uživatele na zvolený kontroler
     * @param array $parameters Pole parametrů, na indexu 0 musí být nezpracovaná URL adresa
     */
    public function process(array $parameters)
    {
        $urlArguments = $this->parseURL($parameters[0]);
        $controllerName;
        
        //Úvodní stránka
        if (empty($urlArguments[0])){$controllerName = 'Index'.self::ControllerExtension;}
        //Jiná kontroler je specifikován
        else {$controllerName = $this->kebabToCamelCase(array_shift($urlArguments)).self::ControllerExtension;}
        
        //Zjištění, zda kontroler existuje
        if (file_exists(self::ControllerFolder.'/'.$controllerName.'.php'))
        {
            $this->controllerToCall = new $controllerName();
        }
        else
        {
            //Neexistující kontroler --> error 404
            $this->redirect('Error404');
        }
        
        $this->controllerToCall->process($urlArguments);
        
        $this->data['title'] = $this->controllerToCall->pageHeader['title'];
        $this->data['description'] = $this->controllerToCall->pageHeader['description'];
        $this->data['keywords'] = $this->controllerToCall->pageHeader['keywords'];
        $this->data['cssFile'] = $this->controllerToCall->pageHeader['cssFile'];
        $this->data['jsFile'] = $this->controllerToCall->pageHeader['jsFile'];
        
        $this->view = 'head';
    }
    
    /**
     * Meoda získávající z nezpracované URL adresy parametry jako pole
     * @param string $url Nezpracovaná URL adresa
     * @return array Pole argumentů následujících po doméně
     */
    private function parseURL(string $url)
    {
        $parsedURL = parse_url($url)['path'];   # Z http(s)://domena.net/abc/def/ghi získá /abc/def/ghi
        $parsedURL = ltrim($parsedURL, '/');    # Odstranění prvního lomítka
        $parsedURL = trim($parsedURL);          # Odstranění mezer na začátku a na konci
        $urlArray = explode('/', $parsedURL);   # Rozbití řetězce do pole podle lomítek
        return $urlArray;
    }
    
    /**
     * Metoda konvertující řetězec v kebab-case do CamelCase
     * @param string $str Řetězec ke konverzi
     * @param bool $capitalizeFirst Má být první písmeno velké (default TRUE)
     * @return string Řetězec konvertovaný do CamelCase
     */
    private function kebabToCamelCase(string $str, bool $capitalizeFirst = true)
    {
        $camel = str_replace('-', ' ', $str);
        $camel = ucwords($camel);
        $camel = str_replace(' ', '', $camel);
        if (!$capitalizeFirst){ $camel = lcfirst($camel); }
        return $camel;
    }
}