<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\DatabaseItems\Group;

/** 
 * Kontroler starající se o výpis stránky pro správu poznávaček správcům třídy, do které patří
 * @author Jan Štěch
 */
class TestsController extends SynchronousController
{
    private array $argumentsToPass = array();

    /**
     * Metoda nastavující hlavičku stránky, data pro pohled a pohled
     * @param array $parameters Pole parametrů pro zpracování kontrolerem, zde může být na první pozici URL název kotnroleru, kterému se má předat řízení
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        self::$data['navigationBar'] = array(
            0 => array(
                'text' => self::$pageHeader['title'],
                'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage/tests'
            )
        );

        //Kontrola, zda nebyla zvolena správa hlášení v nějaké poznávačce nebo její editace
        //Načtení argumentů vztahujících se k této stránce
        //Minimálně 0 (v případě domena.cz/menu/nazev-tridy/manage/tests)
        //Maximálně 2 (v případě domena.cz/menu/nazev-tridy/manage/tests/nazev-poznavacky/akce)
        $testsArguments = array();
        for ($i = 0; $i < 2 && $arg = array_shift($parameters); $i++)
        {
            $testsArguments[] = $arg;
        }
        $argumentCount = count($testsArguments);
        
        if ($argumentCount === 0)
        {
            //Vypisuje se seznam poznávaček - vymaž zvolenou poznávačku
            unset($_SESSION['selection']['group']);
            //Část taky vymaž - sice by neměla být uložena, ale co mi víme ¯\_(ツ)_/¯
            unset($_SESSION['selection']['part']);
        }
        if ($argumentCount > 0)
        {
            //Název poznávačky
            
            //Musí být specifikována i akce
            if ($argumentCount === 1)
            {
                //Přesměrovat na tests bez parametrů
                $this->redirect('menu/'.$_SESSION['selection']['class']->getUrl().'/manage/tests');
            }
            
            //Uložení objektu poznávačky do $_SESSION (pouze pokud už nějaká uložená není)
            if (!isset($_SESSION['selection']['group']))
            {
                //Sice to může vypadat, že by dávalo smysl, kdyby se poznávačka přenastavovala i v případě, že se její URL název neshoduje s parametrem v URL,
                //ale ve skutečnosti by to působilo problémy při přejmenovávání poznávačky na edit stránce (URL stáre obsahuje starý název)
                $_SESSION['selection']['group'] = new Group(false);
                $_SESSION['selection']['group']->initialize(null, $testsArguments[0], $_SESSION['selection']['class'], null, null);
            }

            self::$data['navigationBar'][] = array(
                'text' => $_SESSION['selection']['group']->getName(),
                'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage/tests/'.$_SESSION['selection']['group']->getUrl()
            );
        }
        if ($argumentCount > 1)
        {
            $controllerName = $this->kebabToCamelCase($testsArguments[1]).self::CONTROLLER_EXTENSION;
            $pathToController = $this->controllerExists($controllerName);
            if ($pathToController)
            {
                $this->controllerToCall = new $pathToController();
                $this->argumentsToPass = array_slice($testsArguments, 1);
            }
            else
            {
                //Není specifikována platná akce --> přesměrovat na tests bez parametrů
                $this->redirect('menu/'.$_SESSION['selection']['class']->getUrl().'/manage/tests');
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
            //Kontroler není nastaven --> seznam poznávaček ve třídě
            self::$pageHeader['title'] = 'Správa poznávaček';
            self::$pageHeader['description'] = 'Nástroj pro správce tříd umožnňující snadnou správu poznávaček';
            self::$pageHeader['keywords'] = '';
            self::$pageHeader['cssFiles'] = array('css/css.css');
            self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/ajaxMediator.js','js/tests.js');
            self::$pageHeader['bodyId'] = 'tests';

            self::$data['baseUrl'] = 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage/tests';
            self::$data['groups'] = $_SESSION['selection']['class']->getGroups();
            self::$data['returnButtonLink'] = 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage';
            
            $this->view = 'tests';
        }
    }
}

