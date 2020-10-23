<?php

/** 
 * Kontroler starající se o výpis stránky pro administraci třídy jejím správcům
 * @author Jan Štěch
 */
class ManageController extends Controller
{

    /**
     * Metoda ověřující, zda má uživatel do správy třídy přístup (je její správce nebo administrátor systému) a nastavující hlavičku stránky a pohled
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        try
        {
            if (!isset($_SESSION['selection']['class']))
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_CLASS_NOT_CHOSEN, null, null, array('originFile' => 'ManageController.php', 'displayOnView' => 'manage.phtml'));
            }
        }
        catch (AccessDeniedException $e)
        {
            $this->redirect('error404');
        }
        
        $class = $_SESSION['selection']['class'];
        if (!($class->checkAdmin(UserManager::getId()) || AccessChecker::checkSystemAdmin()))
        {
            $this->redirect('error403');
        }
        
        //Kontrola, zda nebyla zvolena správa hlášení v nějaké poznávačce nebo její editace
        //Načtení argumentů vztahujících se k této stránce
        //Minimálně 0 (v případě domena.cz/menu/nazev-tridy/manage)
        //Maximálně 2 (v případě domena.cz/menu/nazev-tridy/manage/nazev-poznavacky/akce)
        $manageArguments = array();
        for ($i = 0; $i < 2 && $arg = array_shift($parameters); $i++)
        {
            $manageArguments[] = $arg;
        }
        
        $argumentCount = count($manageArguments);
        
        # if ($argumentCount === 0)
        # {
        #     //Vypisuje se obecná správa třídy
        # }
        if ($argumentCount > 0)
        {
            //Název poznávačky

            //Uložení objektu třídy do $_SESSION
            $_SESSION['selection']['group'] = new Group(false);
            $_SESSION['selection']['group']->initialize(urldecode($manageArguments[0]), $_SESSION['selection']['class'], null, null);
        
            //Musí být specifikována i akce
            if ($argumentCount === 1)
            {
                //Přesměrovat na manage bez parametrů
                $this->redirect('menu/'.$_SESSION['selection']['class']->getName().'/manage');
            }
        }
        if ($argumentCount > 1)
        {
            $controllerName = $this->kebabToCamelCase($manageArguments[1]).self::ControllerExtension;
            if (file_exists(self::ControllerFolder.'/'.$controllerName.'.php'))
            {
                $this->controllerToCall = new $controllerName;
                $this->argumentsToPass = array_slice($manageArguments, 3);
            }
            else
            {
                //Není specifikována platná akce --> přesměrovat na manage bez parametrů
                $this->redirect('menu/'.$_SESSION['selection']['class']->getName().'/manage');
            }
        }
        
        if (isset($this->controllerToCall))
        {
            //Kontroler je nastaven --> předat mu řízení
            $this->controllerToCall->process(array());
            
            $this->pageHeader['title'] = $this->controllerToCall->pageHeader['title'];
            $this->pageHeader['description'] = $this->controllerToCall->pageHeader['description'];
            $this->pageHeader['keywords'] = $this->controllerToCall->pageHeader['keywords'];
            $this->pageHeader['cssFiles'] = $this->controllerToCall->pageHeader['cssFiles'];
            $this->pageHeader['jsFiles'] = $this->controllerToCall->pageHeader['jsFiles'];
            $this->pageHeader['bodyId'] = $this->controllerToCall->pageHeader['bodyId'];
            
            $this->view = 'manageAction';
        }
        else
        {
            //Kontroler není nastaven --> obecnou správu třídy
            $this->pageHeader['title'] = 'Správa třídy';
            $this->pageHeader['description'] = 'Nástroj pro vlastníky tříd umožňující snadnou správu poznávaček a členů.';
            $this->pageHeader['keywords'] = '';
            $this->pageHeader['cssFiles'] = array('css/css.css');
            $this->pageHeader['jsFiles'] = array('js/generic.js','js/manage.js');
            $this->pageHeader['bodyId'] = 'manage';
            
            $this->data['classId'] = $_SESSION['selection']['class']->getId();
            $this->data['className'] = $_SESSION['selection']['class']->getName();
            $this->data['classStatus'] = $_SESSION['selection']['class']->getStatus();
            $this->data['classCode'] = $_SESSION['selection']['class']->getCode();
            $this->data['members'] = $_SESSION['selection']['class']->getMembers(false); //false zajistí, že se nezobrazí právě přihlášený uživatel
            $this->data['groups'] = $_SESSION['selection']['class']->getGroups();
            
            $this->view = 'manage';
        }
    }
}