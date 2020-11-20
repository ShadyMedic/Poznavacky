<?php
/**
 * Kontroler starající se o zobrazení layoutu pro všechny stránky kromě indexu
 * @author Jan Štěch
 */
class MenuController extends Controller
{
    private $argumentsToPass = array();
    
    /**
     * Metoda rozhodující o tom, co se v layoutu zadaném v menu.phtml robrazí podle počtu specifikovaných argumentů v URL
     * Metoda nejprve zkontroluje, zda je uživatel přihlášen
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        //Kontrola, zda je uživatel přihlášen
        $aChecker = new AccessChecker();
        if (!$aChecker->checkUser())
        {
            //Přihlášení uživatele vypršelo
            //Kontrola instantcookie sezení
            if (isset($_COOKIE['instantLogin']))
            {
                try
                {
                    $userLogger = new LoginUser();
                    $userLogger->processCookieLogin($_COOKIE['instantLogin']);
                    //Přihlášení obnoveno
                }
                catch(AccessDeniedException $e)
                {
                    //Chybný kód
                    //Vymaž cookie s neplatným kódem
                    setcookie('instantLogin', null, -1, '/');
                    unset($_COOKIE['instantLogin']);
                    
                    $this->redirect('');
                }
            }
            else
            {
                $this->redirect('');
            }
        }
        
        //Načtení argumentů vztahujících se k této stránce
        //Minimálně 0 (v případě domena.cz/menu)
        //Maximálně 5 (v případě domena.cz/menu/nazev-tridy/nazev-poznavacky/nazev-casti/akce/ajax-kontroller)
        $menuArguments = array();
        for ($i = 0; $i < 5 && $arg = array_shift($parameters); $i++)
        {
            $menuArguments[] = $arg;
        }
        
        $argumentCount = count($menuArguments);
      
        if ($argumentCount === 0)
        {
            //Vypisují se třídy
            
            //Vymazání objektů skladujících vybranou složku ze $_SESSION
            $this->unsetSelection(true, true, true);
        }
        if ($argumentCount > 0)
        {
            $controllerName = $this->kebabToCamelCase($menuArguments[0]).self::ControllerExtension;
            if (file_exists(self::ControllerFolder.'/'.$controllerName.'.php') && $argumentCount === 1)
            {
                //AdministrateController nebo AccountSettingsController
                $this->controllerToCall = new $controllerName;
                
                //Vymazání objektů skladujících vybranou složku ze $_SESSION
                $this->unsetSelection(true, true, true);
                $this->argumentsToPass = array_slice($menuArguments, 1);
            }
            else
            {
                //Název třídy
                //Kontrola, zda právě zvolený název souhlasí s názvem třídy uložené v $_SESSION
                if (!isset($_SESSION['selection']['class']) || urldecode($menuArguments[0]) !== $_SESSION['selection']['class']->getName())
                {
                    //Uložení objektu třídy do $_SESSION
                    $_SESSION['selection']['class'] = new ClassObject(false, 0);
                    $_SESSION['selection']['class']->initialize(null, $menuArguments[0]);
                    $_SESSION['selection']['class']->load();
                    
                    //Vymazání objektů skladujících vybranou poznávačku a část ze $_SESSION
                    $this->unsetSelection(true, true);
                }
                
                if ($argumentCount === 1)
                {
                    //Vymazání objektů skladujících vybranou poznávačku a část ze $_SESSION
                    $this->unsetSelection(true, true);
                }
            }
        }
        if ($argumentCount > 1 && !isset($this->controllerToCall))
        {
            $controllerName = $this->kebabToCamelCase($menuArguments[1]).self::ControllerExtension;
            if (file_exists(self::ControllerFolder.'/'.$controllerName.'.php') && ($argumentCount === 2 || $controllerName === 'ManageController'))
            {
                //ManageController / LeaveController
                $this->controllerToCall = new $controllerName();
                
                //Vymazání objektů skladujících vybranou poznávačku a část ze $_SESSION
                $this->unsetSelection(true, true);
                $this->argumentsToPass = array_slice($menuArguments, 2);
            }
            else
            {
                //Název poznávačky
                //Kontrola, zda právě zvolený název souhlasí s názvem poznávačky uložené v $_SESSION
                if (!isset($_SESSION['selection']['group']) || urldecode($menuArguments[1]) !== @$_SESSION['selection']['group']->getName())
                {
                    //Uložení objektu poznávačky do $_SESSION
                    $_SESSION['selection']['group'] = new Group(false);
                    $_SESSION['selection']['group']->initialize(null, $menuArguments[1], $_SESSION['selection']['class'], null, null);
                    
                    //Vymazání objektů skladujících vybranou část ze $_SESSION
                    $this->unsetSelection(true);
                }
                
                if ($argumentCount === 2)
                {
                    //Vymazání objektů skladujících vybranou část ze $_SESSION
                    $this->unsetSelection(true);
                }
            }
        }
        if ($argumentCount > 2 && !isset($this->controllerToCall))
        {
            //Jsou zvoleny všechny části najednou?
            $controllerName = $this->kebabToCamelCase($menuArguments[2]).self::ControllerExtension;
            if (file_exists(self::ControllerFolder.'/'.$controllerName.'.php'))
            {
                //Ano
                $this->controllerToCall = new $controllerName();
                $this->argumentsToPass = array_slice($menuArguments, 3);
                
                //Vymazání objektu skladujícího vybranou část ze $_SESSION
                $this->unsetSelection(true);
            }
            else
            {
                //Ne --> v dalším argumentu musí být specifikována akce
                if ($argumentCount === 3)
                {
                    //Je specifikována část, ale ne akce --> návrat na seznam částí
                    $this->redirect('menu/'.$_SESSION['selection']['class']->getName().'/'.$_SESSION['selection']['group']->getName());
                }
                
                //Nastavení části (pouze, pokud nejsou vybrány všechny části najednou)
                //Kontrola, zda právě zvolený název souhlasí s názvem třídy uložené v $_SESSION
                if (!isset($_SESSION['selection']['part']) || urldecode($menuArguments[2]) !== @$_SESSION['selection']['part']->getName())
                {
                    //Uložení objektu části do $_SESSION
                    $_SESSION['selection']['part'] = new Part(false);
                    $_SESSION['selection']['part']->initialize(null, $menuArguments[2], $_SESSION['selection']['group'], null, null, null);
                }
            }
        }
        if ($argumentCount > 3 && !isset($this->controllerToCall))
        {
            //Akce pro část
            $controllerName = $this->kebabToCamelCase($menuArguments[3]).self::ControllerExtension;
            if (file_exists(self::ControllerFolder.'/'.$controllerName.'.php'))
            {
                $this->controllerToCall = new $controllerName();
                $this->argumentsToPass = array_slice($menuArguments, 4);
            }
            else
            {
                //Neplatný kontroler
                $this->redirect('error404');
            }
        }
        
        if (isset($this->controllerToCall))
        {
            //Kontroler je nastaven --> předat posbírané argumenty dál
            $this->controllerToCall->process($this->argumentsToPass);
            $this->pageHeader['bodyId'] = $this->controllerToCall->pageHeader['bodyId'];
        }
        else
        {
            //Kontroler není nastaven --> vypsat tabulku na menu stránkce
            $this->pageHeader['bodyId'] = 'menu';
            $controllerName = 'MenuTable'.self::ControllerExtension;
            $this->controllerToCall = new $controllerName();
            $this->controllerToCall->process(array());
        }
        
        $this->pageHeader['title'] = $this->controllerToCall->pageHeader['title'];
        $this->pageHeader['description'] = $this->controllerToCall->pageHeader['description'];
        $this->pageHeader['keywords'] = $this->controllerToCall->pageHeader['keywords'];
        $this->pageHeader['cssFiles'] = $this->controllerToCall->pageHeader['cssFiles'];
        $this->pageHeader['jsFiles'] = $this->controllerToCall->pageHeader['jsFiles'];
        
        $this->data['loggedUserName'] = UserManager::getName();
        $this->data['adminLogged'] = $aChecker->checkSystemAdmin();
        
        $this->view = 'menu';
    }
    
    /**
     * Metoda odstraňující ze $_SESSION objekty ukládající vybranou třídu, poznávačku, nebo její část
     * @param bool $unsetPart TRUE, pokud se má odstranit část; defaultně FALSE
     * @param bool $unsetGroup TRUE, pokud se má odstranit poznávačka; defaultně FALSE
     * @param bool $unsetClass TRUE, pokud se má odstranit třída; defaultně FALSE
     */
    private function unsetSelection(bool $unsetPart = false, bool $unsetGroup = false, bool $unsetClass = false): void
    {
        if ($unsetPart){ unset($_SESSION['selection']['part']); }
        if ($unsetGroup){ unset($_SESSION['selection']['group']); }
        if ($unsetClass){ unset($_SESSION['selection']['class']); }
    }
}