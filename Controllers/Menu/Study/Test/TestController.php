<?php
namespace Poznavacky\Controllers\Menu\Study\Test;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\Statics\UserManager;

/** 
 * Kontroler starající se o výpis stránky pro testování
 * @author Jan Štěch
 */
class TestController extends Controller
{
    /**
     * Metoda ověřující, zda má uživatel do třídy přístup a nastavující hlavičku stránky a pohled
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        $class = $_SESSION['selection']['class'];
        
        //Kontrola přístupu
        if (!$class->checkAccess(UserManager::getId()))
        {
            $this->redirect('error403');
        }
        
        $this->pageHeader['title'] = 'Testovat';
        $this->pageHeader['description'] = 'Vyzkoušejte si, jak dobře znáte přírodniny v poznávačce pomocí náhodného testování';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/ajaxMediator.js','js/test.js','js/reportForm.js','js/menu.js');
        $this->pageHeader['bodyId'] = 'test';
        
        $controllerName = "nonexistant-controller";
        if (isset($parameters[0])){ $controllerName = $this->kebabToCamelCase($parameters[0]).self::ControllerExtension; }
        $pathToController = $this->controllerExists($controllerName);
        if ($pathToController)
        {
            //URL obsajuje požadavek na další kontroler používaný na test stránce
            $this->controllerToCall = new $pathToController();
            $this->controllerToCall->process($parameters);
            
            $this->pageHeader['title'] = $this->controllerToCall->pageHeader['title'];
            $this->pageHeader['description'] = $this->controllerToCall->pageHeader['description'];
            $this->pageHeader['keywords'] = $this->controllerToCall->pageHeader['keywords'];
            $this->pageHeader['cssFiles'] = $this->controllerToCall->pageHeader['cssFiles'];
            $this->pageHeader['jsFiles'] = $this->controllerToCall->pageHeader['jsFiles'];
            $this->pageHeader['bodyId'] = $this->controllerToCall->pageHeader['bodyId'];
        }
        
        $this->view = 'test';
    }
}

