<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;

/**
 * Kontroler starající se o výpis stránky pro administraci třídy jejím správcům
 * @author Jan Štěch
 */
class ManageController extends SynchronousController
{
    private array $argumentsToPass = array();

    /**
     * Metoda ověřující, zda má uživatel do správy třídy přístup (je její správce nebo administrátor systému) a nastavující hlavičku stránky a pohled
     * @param array $parameters Pole parametrů pro zpracování kontrolerem, zde může být prvním prvkem URL název kontroleru, kterému se má předat řízení
     * @see SynchronousController::process()
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
        if (!($class->checkAdmin(UserManager::getId()) || $aChecker->checkSystemAdmin()))
        {
            $this->redirect('error403');
        }

        self::$data['navigationBar'][] = array(
            'text' => 'Správa třídy',
            'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage'
        );

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
            $controllerName = $this->kebabToCamelCase($manageArguments[0]).self::CONTROLLER_EXTENSION;
            $pathToController = $this->controllerExists($controllerName);
            if ($pathToController)
            {
                $this->controllerToCall = new $pathToController();
                $this->argumentsToPass = array_slice($manageArguments, 1);
            }
            else
            {
                //Není specifikována platná akce --> přesměrovat na manage bez parametrů
                $this->redirect('menu/'.$_SESSION['selection']['class']->getUrl().'/manage');
            }
        }

        if (isset($this->controllerToCall))
        {
            //Kontroler je nastaven --> předat mu řízení
            $this->controllerToCall->process($this->argumentsToPass);
            self::$data['navigationBar'] = array_merge(self::$data['navigationBar'], $this->controllerToCall::data['navigationBar']);

            $this->view = 'inherit';
        }
        else
        {
            //Kontroler není nastaven --> obecná správa třídy
            self::$pageHeader['title'] = 'Správa třídy';
            self::$pageHeader['description'] = 'Nástroj pro správce tříd umožňující snadnou správu třídy';
            self::$pageHeader['keywords'] = '';
            self::$pageHeader['cssFiles'] = array('css/css.css');
            self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/menu.js', 'js/ajaxMediator.js','js/manage.js');
            self::$pageHeader['bodyId'] = 'manage';

            self::$data['baseUrl'] = 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage';
            self::$data['classId'] = $_SESSION['selection']['class']->getId();
            self::$data['className'] = $_SESSION['selection']['class']->getName();
            self::$data['classStatus'] = $_SESSION['selection']['class']->getStatus();
            self::$data['classCode'] = $_SESSION['selection']['class']->getCode();

            $this->view = 'manage';
        }
    }
}

