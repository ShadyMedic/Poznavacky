<?php
/**
 * Kontroler starající se o zobrazení layoutu pro všechny stránky kromě indexu
 * @author Jan Štěch
 */
class MenuController extends Controller
{
    private $chosenFolder = array();
    private $argumentsToPass = array();
    
    /**
     * Metoda rozhodující o tom, co se v layoutu zadaném v menu.phtml robrazí podle počtu specifikovaných argumentů v URL
     * Metoda nejprve zkontroluje, zda je uživatel přihlášen
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        //Kontrola, zda je uživatel přihlášen
        if (!AccessChecker::checkUser())
        {
            //Přihlášení uživatele vypršelo
            //Kontrola instantcookie sezení
            if (isset($_COOKIE['instantLogin']))
            {
                try
                {
                    LoginUser::processCookieLogin($_COOKIE['instantLogin']);
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
        
        //Načtení argumentů vztahujících se k této stránkce
        //Minimálně 0 (v případě domena.cz/menu)
        //Maximálně 5 (v případě domena.cz/menu/nazev-tridy/nazev-poznavacky/nazev-casti/akce/ajax-kontroller)
        $menuArguments = array();
        for ($i = 0; $i < 5 && $arg = array_shift($parameters); $i++)
        {
            $menuArguments[] = $arg;
        }
        
        $argumentCount = count($menuArguments);

        #if ($argumentCount === 0)
        #{
        #    Vypsání tříd
        #}
        if ($argumentCount > 0)
        {
            $controllerName = $this->kebabToCamelCase($menuArguments[0]).self::ControllerExtension;
            if (file_exists(self::ControllerFolder.'/'.$controllerName.'.php') && $argumentCount === 1)
            {
                //AdministrateController
                $this->controllerToCall = new $controllerName;
            }
            else
            {
                //Název třídy
                $this->chosenFolder['class'] = urldecode($menuArguments[0]);
            }
        }
        if ($argumentCount > 1)
        {
            $controllerName = $this->kebabToCamelCase($menuArguments[1]).self::ControllerExtension;
            if (file_exists(self::ControllerFolder.'/'.$controllerName.'.php') && $argumentCount === 2)
            {
                //ManageController / LeaveController
                $this->controllerToCall = new $controllerName;
            }
            else
            {
                //Název poznávačky
                $this->chosenFolder['group'] = urldecode($menuArguments[1]);
            }
        }
        if ($argumentCount > 2)
        {
            //Jsou zvoleny všechny části najednou?
            $controllerName = $this->kebabToCamelCase($menuArguments[2]).self::ControllerExtension;
            if (file_exists(self::ControllerFolder.'/'.$controllerName.'.php'))
            {
                //Ano
                $this->controllerToCall = new $controllerName;
                $this->argumentsToPass = array_slice($menuArguments, 3);
            }
            else
            {
                //Ne --> v dalším argumentu musí být specifikována akce
                if ($argumentCount === 3)
                {
                    //Je specifikována část, ale ne akce --> návrat na seznam částí
                    $this->redirect('menu/'.$this->chosenFolder['class'].'/'.$this->chosenFolder['group']);
                }
                
                //Nastavení části (pouze, pokud nejsou vybrány všechny části najednou)
                $this->chosenFolder['part'] = urldecode($menuArguments[2]);
            }
        }
        if ($argumentCount > 3)
        {
            //Akce pro část
            $controllerName = $this->kebabToCamelCase($menuArguments[3]).self::ControllerExtension;
            if (file_exists(self::ControllerFolder.'/'.$controllerName.'.php'))
            {
                $this->controllerToCall = new $controllerName;
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
            $this->controllerToCall->process(array_merge($this->argumentsToPass, $this->chosenFolder));
            $this->pageHeader['bodyId'] = $this->controllerToCall->pageHeader['bodyId'];
        }
        else
        {
            //Kontroler není nastaven --> vypsat tabulku na menu stránkce
            $this->pageHeader['bodyId'] = 'menu';
            $controllerName = 'MenuTable'.self::ControllerExtension;
            $this->controllerToCall = new $controllerName;
            $this->controllerToCall->process($this->chosenFolder);
        }
        
        $this->pageHeader['title'] = $this->controllerToCall->pageHeader['title'];
        $this->pageHeader['description'] = $this->controllerToCall->pageHeader['description'];
        $this->pageHeader['keywords'] = $this->controllerToCall->pageHeader['keywords'];
        $this->pageHeader['cssFile'] = $this->controllerToCall->pageHeader['cssFile'];
        $this->pageHeader['jsFile'] = $this->controllerToCall->pageHeader['jsFile'];
        
        $this->data['loggedUserName'] = UserManager::getName();
        $this->data['adminLogged'] = AccessChecker::checkSystemAdmin(UserManager::getId());
        
        $this->view = 'menu';
    }
}