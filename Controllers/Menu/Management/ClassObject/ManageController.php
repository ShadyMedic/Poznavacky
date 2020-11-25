<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

/** 
 * Kontroler starající se o výpis stránky pro administraci třídy jejím správcům
 * @author Jan Štěch
 */
class ManageController extends Controller
{
    private $argumentsToPass = array();
    
    /**
     * Metoda ověřující, zda má uživatel do správy třídy přístup (je její správce nebo administrátor systému) a nastavující hlavičku stránky a pohled
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        try
        {
            if (!isset($_SESSION['selection']['class']))
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_CLASS_NOT_CHOSEN, null, null);
            }
        }
        catch (AccessDeniedException $e)
        {
            $this->redirect('error404');
        }
        
        $class = $_SESSION['selection']['class'];
        $aChecker = new AccessChecker();
        if (!($class->checkAdmin(UserManager::getId()) || $aChecker::checkSystemAdmin()))
        {
            $this->redirect('error403');
        }
        
        //Kontrola, zda nebyla zvolena správa členů nebo poznávaček
        //Načtení argumentů vztahujících se k této stránce
        //Minimálně 0 (v případě domena.cz/menu/nazev-tridy/manage)
        //Maximálně 3 (v případě domena.cz/menu/nazev-tridy/manage/tests/nazev-poznavacky/akce)
        $manageArguments = array();
        for ($i = 0; $i < 3 && $arg = array_shift($parameters); $i++)
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
            $controllerName = $this->kebabToCamelCase($manageArguments[0]).self::ControllerExtension;
            if (file_exists(self::ControllerFolder.'/'.$controllerName.'.php'))
            {
                $this->controllerToCall = new $controllerName;
                $this->argumentsToPass = array_slice($manageArguments, 1);
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
            $this->controllerToCall->process($this->argumentsToPass);
            
            $this->pageHeader['title'] = $this->controllerToCall->pageHeader['title'];
            $this->pageHeader['description'] = $this->controllerToCall->pageHeader['description'];
            $this->pageHeader['keywords'] = $this->controllerToCall->pageHeader['keywords'];
            $this->pageHeader['cssFiles'] = $this->controllerToCall->pageHeader['cssFiles'];
            $this->pageHeader['jsFiles'] = $this->controllerToCall->pageHeader['jsFiles'];
            $this->pageHeader['bodyId'] = $this->controllerToCall->pageHeader['bodyId'];
            
            $this->data['returnButtonLink'] = $this->controllerToCall->data['returnButtonLink'];
            
            $this->view = 'manageAction';
        }
        else
        {
            //Kontroler není nastaven --> obecná správa třídy
            $this->pageHeader['title'] = 'Správa třídy';
            $this->pageHeader['description'] = 'Nástroj pro správce tříd umožňující snadnou správu třídy';
            $this->pageHeader['keywords'] = '';
            $this->pageHeader['cssFiles'] = array('css/css.css');
            $this->pageHeader['jsFiles'] = array('js/generic.js','js/ajaxMediator.js','js/manage.js');
            $this->pageHeader['bodyId'] = 'manage';
            
            $this->data['classId'] = $_SESSION['selection']['class']->getId();
            $this->data['className'] = $_SESSION['selection']['class']->getName();
            $this->data['classStatus'] = $_SESSION['selection']['class']->getStatus();
            $this->data['classCode'] = $_SESSION['selection']['class']->getCode();
            
            $this->view = 'manage';
        }
    }
}

