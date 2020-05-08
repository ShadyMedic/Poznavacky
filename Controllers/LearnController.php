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
        if (!AccessChecker::checkAccess(UserManager::getId(), ClassManager::getIdByName($parameters[0])))
        {
            $this->redirect('error403');
        }
        
        $this->pageHeader['title'] = 'Učit se';
        $this->pageHeader['description'] = 'Učte se na poznávačku podle svého vlastního tempa';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFile'] = 'css/css.css';
        $this->pageHeader['jsFile'] = 'js/learn.js';
        $this->pageHeader['bodyId'] = 'learn';
        
        $this->view = 'learn';
    }
}