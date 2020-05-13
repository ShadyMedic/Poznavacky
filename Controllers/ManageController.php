<?php

/** 
 * Kontroler starající se o výpis stránky pro administraci třídy jejím správcům
 * @author Jan Štěch
 */
class ManageController extends Controller
{

    /**
     * Metoda ověřující, zda má uživatel do správy třídy přístup a nastavující hlavičku stránky a pohled
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        try
        {
            if (mb_strlen($parameters[0]) === 0)
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_CLASS_NOT_CHOSEN, null, null, array('originFile' => 'ManageController.php', 'displayOnView' => 'manage.phtml'));
            }
        }
        catch (AccessDeniedException $e)
        {
            $this->redirect('error404');
        }
        
        $class = new ClassObject(0, $parameters[0]);
        if (!$class->checkAdmin(UserManager::getId()))
        {
            $this->redirect('error403');
        }
        
        $this->pageHeader['title'] = 'Správa třídy';
        $this->pageHeader['description'] = 'Nástroj pro vlastníky tříd umožňující snadnou správu poznávaček a členů.';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFile'] = 'css/css.css';
        $this->pageHeader['jsFile'] = 'js/manage.js';
        $this->pageHeader['bodyId'] = 'manage';
        
        $this->view = 'manage';
    }
}