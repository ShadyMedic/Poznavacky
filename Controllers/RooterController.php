<?php
namespace Poznavacky\Controllers;

/**
 * Třída směrovače přesměrovávající uživatele z index.php na správný kontroler
 * @author Jan Štěch       
 */
class RooterController extends Controller
{    
    /**
     * Metoda zpracovávající zadanou URL adresu a přesměrovávající uživatele na zvolený kontroler
     * @param array $parameters Pole parametrů, na indexu 0 musí být nezpracovaná URL adresa
     */
    public function process(array $parameters): void
    {
        $urlArguments = $this->parseURL($parameters[0]);
        $controllerName = null;
        
        //Úvodní stránka
        if (empty($urlArguments[0])){$controllerName = 'Index'.self::CONTROLLER_EXTENSION;}
        //Jiná kontroler je specifikován
        else {$controllerName = $this->kebabToCamelCase(array_shift($urlArguments)).self::CONTROLLER_EXTENSION;}
        //Zjištění, zda kontroler existuje
        $pathToController = $this->controllerExists($controllerName);
        if ($pathToController)
        {
            $this->controllerToCall = new $pathToController();
        }
        else
        {
            //Neexistující kontroler --> error 404
            $this->redirect('error404');
        }
        
        $this->controllerToCall->process($urlArguments);
        
        $this->data['title'] = $this->controllerToCall->pageHeader['title'];
        $this->data['description'] = $this->controllerToCall->pageHeader['description'];
        $this->data['keywords'] = $this->controllerToCall->pageHeader['keywords'];
        $this->data['cssFiles'] = $this->controllerToCall->pageHeader['cssFiles'];
        $this->data['jsFiles'] = $this->controllerToCall->pageHeader['jsFiles'];
        $this->data['bodyId'] = $this->controllerToCall->pageHeader['bodyId'];
        $this->data['messages'] = $this->getMessages();
        $this->data['currentYear'] = date('Y');
        
        $this->view = 'head';
    }
    
    /**
     * Metoda načítající hlášky pro uživatele uložené v $_SESSION a přidávající jejich obsah do dat, které jsou později předány pohledu
     * Hlášky jsou poté ze sezení vymazány
     */
    protected function getMessages(): array
    {
        if (isset($_SESSION['messages']))
        {
            $messages = $_SESSION['messages'];
            $messagesData = array();
            foreach ($messages as $messageBox)
            {
                $messagesData[] = $messageBox->getData();
            }
            $this->clearMessages();
            return $messagesData;
        }
        else
        {
            return array();
        }
    }
    
    /**
     * Metoda odstraňující všechny hlášky pro uživatele uloženy v $_SESSION
     */
    protected function clearMessages(): void
    {
        unset($_SESSION['messages']);
    }
    
    /**
     * Meoda získávající z nezpracované URL adresy parametry jako pole
     * @param string $url Nezpracovaná URL adresa
     * @return array Pole argumentů následujících po doméně
     */
    private function parseURL(string $url): array
    {
        $parsedURL = parse_url($url)['path'];   # Z http(s)://domena.net/abc/def/ghi získá /abc/def/ghi
        $parsedURL = ltrim($parsedURL, '/');    # Odstranění prvního lomítka
        $parsedURL = trim($parsedURL);          # Odstranění mezer na začátku a na konci
        $urlArray = explode('/', $parsedURL);   # Rozbití řetězce do pole podle lomítek
        return $urlArray;
    }
}

