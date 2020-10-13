<?php
/** 
 * Kontroler starající se o výpis stránky pro učení se
 * @author Jan Štěch
 */
class LearnController extends Controller
{

    /**
     * Metoda ověřující, zda má uživatel do třídy přístup a nastavující hlavičku stránky a pohled
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        $class = $_SESSION['selection']['class'];
        $group = $_SESSION['selection']['group'];
        if (isset($_SESSION['selection']['part']))
        {
            $part = $_SESSION['selection']['part'];
            $allParts = false;
        }
        else
        {
            $allParts = true;
        }
        
        //Kontrola přístupu
        if (!$class->checkAccess(UserManager::getId()))
        {
            $this->redirect('error403');
        }
        
        if ($allParts){ $this->data['naturals'] = $group->getNaturals(); }
        else { $this->data['naturals'] = $part->getNaturals(); }
        
        $this->pageHeader['title'] = 'Učit se';
        $this->pageHeader['description'] = 'Učte se na poznávačku podle svého vlastního tempa';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/learn.js','js/report.js', 'js/menu.js');
        $this->pageHeader['bodyId'] = 'learn';
        
        $controllerName = "nonexistant-controller";
        if (isset($parameters[0])){ $controllerName = $this->kebabToCamelCase($parameters[0]).self::ControllerExtension; }
        if (file_exists(self::ControllerFolder.'/'.$controllerName.'.php'))
        {
            //URL obsajuje požadavek na další kontroler používaný na menu stránce
            $this->controllerToCall = new $controllerName;
            $this->controllerToCall->process($parameters);
            
            $this->pageHeader['title'] = $this->controllerToCall->pageHeader['title'];
            $this->pageHeader['description'] = $this->controllerToCall->pageHeader['description'];
            $this->pageHeader['keywords'] = $this->controllerToCall->pageHeader['keywords'];
            $this->pageHeader['cssFiles'] = $this->controllerToCall->pageHeader['cssFiles'];
            $this->pageHeader['jsFiles'] = $this->controllerToCall->pageHeader['jsFiles'];
            $this->pageHeader['bodyId'] = $this->controllerToCall->pageHeader['bodyId'];
        }
        
        $this->view = 'learn';
    }
}