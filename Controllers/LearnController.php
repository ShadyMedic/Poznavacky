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
        $class = new ClassObject(0, $parameters['class']);
        $group = new Group(0, $parameters['group'], $class);
        if (isset($parameters['part']))
        {
            $part = new Part(0, $parameters['part'], $group);
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
        $this->pageHeader['cssFile'] = 'css/css.css';
        $this->pageHeader['jsFile'] = 'js/learn.js';
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
            $this->pageHeader['cssFile'] = $this->controllerToCall->pageHeader['cssFile'];
            $this->pageHeader['jsFile'] = $this->controllerToCall->pageHeader['jsFile'];
            $this->pageHeader['bodyId'] = $this->controllerToCall->pageHeader['bodyId'];
        }
        
        $this->view = 'learn';
    }
}