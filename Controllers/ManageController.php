<?php

/** 
 * Kontroler starající se o výpis stránky pro administraci třídy jejím správcům
 * @author Jan Štěch
 */
class ManageController extends Controller
{

    /**
     * Metoda ověřující, zda má uživatel do správy třídy přístup (je její správce nebo administrátor systému) a nastavující hlavičku stránky a pohled
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        try
        {
            if (!isset($_SESSION['selection']['class']))
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_CLASS_NOT_CHOSEN, null, null, array('originFile' => 'ManageController.php', 'displayOnView' => 'manage.phtml'));
            }
        }
        catch (AccessDeniedException $e)
        {
            $this->redirect('error404');
        }
        
        $class = $_SESSION['selection']['class'];
        if (!($class->checkAdmin(UserManager::getId()) || AccessChecker::checkSystemAdmin()))
        {
            $this->redirect('error403');
        }
        
        $this->pageHeader['title'] = 'Správa třídy';
        $this->pageHeader['description'] = 'Nástroj pro vlastníky tříd umožňující snadnou správu poznávaček a členů.';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/manage.js');
        $this->pageHeader['bodyId'] = 'manage';
        
        $this->view = 'manage';
    }
}