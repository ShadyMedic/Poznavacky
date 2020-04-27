<?php

/** 
 * Kontroler starající se o zobrazení layoutu pro všechny stránky kromě indexu
 * @author Jan Štěch
 */
class MenuController extends Controller
{
    private $chosenFolder = array();
    
    /**
     * Metoda rozhodující o tom, co se v layoutu zadaném v menu.phtml robrazí podle počtu specifikovaných argumentů v URL
     * @see Controller::process()
     */
    public function process(array $parameters)
    {   
        //Načtení argumentů vztahujících se k této stránkce
        //Minimálně 0 (v případě domena.cz/menu)
        //Maximálně 4 (v případě domena.cz/menu/nazev-tridy/nazev-poznavacky/nazev-casti/akce)
        $menuArguments = array();
        for ($i = 0; $i < 4 && $arg = array_shift($parameters); $i++)
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
            $controllerName = $this->kebabToCamelCase($menuArguments[0]).self::ControllerExtension.'.php';
            if (file_exists(self::ControllerFolder.'/'.$controllerName) && $argumentCount === 1)
            {
                //ManagementController
                $this->controllerToCall = $controllerName;
            }
            else
            {
                //Název třídy
                $this->chosenFolder[] = $menuArguments[0];
            }
        }
        if ($argumentCount > 1)
        {
            $controllerName = $this->kebabToCamelCase($menuArguments[1]).self::ControllerExtension.'.php';
            if (file_exists(self::ControllerFolder.'/'.$controllerName) && $argumentCount === 2)
            {
                //ManageController
                $this->controllerToCall = $controllerName;
            }
            else
            {
                //Název poznávačky
                $this->chosenFolder[] = $menuArguments[1];
            }
        }
        if ($argumentCount > 2)
        {
            //Nastavení části
            $this->chosenFolder[] = $menuArguments[2];
        }
        if ($argumentCount > 3)
        {
            //Akce pro část
            $controllerName = $this->kebabToCamelCase($menuArguments[3]).self::ControllerExtension.'.php';
            if (file_exists(self::ControllerFolder.'/'.$controllerName))
            {
                $this->controllerToCall = $controllerName;
            }
        }
        
        if (isset($this->controllerToCall))
        {
            //Kontroler je nastaven --> předat posbírané argumenty dál
            $this->controllerToCall->process($parameters, $this->chosenFolder);
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
        
        $this->view = 'menu';
    }
}