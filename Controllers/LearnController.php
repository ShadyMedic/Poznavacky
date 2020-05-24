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
        $class = new ClassObject(0, $parameters[0]);
        $group = new Group(0, $parameters[1], $class);
        if (isset($parameters[2]))
        {
            $part = new Part(0, $parameters[2], $group);
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
        
        $this->view = 'learn';
    }
}