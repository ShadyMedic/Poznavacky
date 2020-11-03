<?php

/** 
 * Kontroler starající se o výpis stránky pro správu poznávaček správcům třídy, do které patří
 * @author Jan Štěch
 */
class TestsController extends Controller
{
    private $argumentsToPass = array();
    
    /**
     * Metoda nastavující hlavičku stránky, data pro pohled a pohled
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
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
        
        # if ($argumentCount === 0)
        # {
        #     //Vypisuje se seznam poznávaček
        # }
        if ($argumentCount > 0)
        {
            //Název poznávačky

            //Uložení objektu poznávačky do $_SESSION
            $_SESSION['selection']['group'] = new Group(false);
            $_SESSION['selection']['group']->initialize(urldecode($testsArguments[0]), $_SESSION['selection']['class'], null, null);
        
            //Musí být specifikována i akce
            if ($argumentCount === 1)
            {
                //Přesměrovat na tests bez parametrů
                $this->redirect('menu/'.$_SESSION['selection']['class']->getName().'/manage/tests');
            }
        }
        if ($argumentCount > 1)
        {
            $controllerName = $this->kebabToCamelCase($testsArguments[1]).self::ControllerExtension;
            if (file_exists(self::ControllerFolder.'/'.$controllerName.'.php'))
            {
                $this->controllerToCall = new $controllerName;
                $this->argumentsToPass = array_slice($testsArguments, 1);
            }
            else
            {
                //Není specifikována platná akce --> přesměrovat na tests bez parametrů
                $this->redirect('menu/'.$_SESSION['selection']['class']->getName().'/manage/tests');
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
            
            $this->view = 'inherit';
        }
        else
        {
            //Kontroler není nastaven --> seznam poznávaček ve třídě
            $this->pageHeader['title'] = 'Správa poznávaček';
            $this->pageHeader['description'] = 'Nástroj pro správce tříd umožnňující snadnou správu poznávaček';
            $this->pageHeader['keywords'] = '';
            $this->pageHeader['cssFiles'] = array('css/css.css');
            $this->pageHeader['jsFiles'] = array('js/generic.js','js/tests.js');
            $this->pageHeader['bodyId'] = 'tests';
            
            $this->data['groups'] = $_SESSION['selection']['class']->getGroups();
            $this->data['returnButtonLink'] = 'menu/'.$_SESSION['selection']['class']->getName().'/manage';
            
            $this->view = 'tests';
        }
    }
}