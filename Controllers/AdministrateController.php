<?php

/** 
 * Kontroler starající se o výpis administrační stránky správcům služby
 * @author Jan Štěch
 */
class AdministrateController extends Controller
{

    /**
     * Metoda ověřující, zda má uživatel do administrace přístup a nastavující hlavičku stránky a pohled
     * @see Controller::process()
     */
    public function process(array $paremeters)
    {
        $aChecker = new AccessChecker();
        if (!$aChecker::checkSystemAdmin())
        {
            $this->redirect('error403');
        }
        
        $administration = new Administration();
        
        $this->data['loggedAdminName'] = UserManager::getName();
        
        $this->data['users'] = $administration->getAllUsers(false);
        $this->data['classes'] = $administration->getAllClasses();
        $this->data['reports'] = $administration->getAdminReports();
        $this->data['userNameChangeRequests'] = $administration->getUserNameChangeRequests();
        $this->data['classNameChangeRequests'] = $administration->getClassNameChangeRequests();
        
        $this->pageHeader['title'] = 'Správa služby';
        $this->pageHeader['description'] = 'Nástroj pro administrátory služby umožňující snadnou správu různých součástí systému.';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/private.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/administrate.js', 'js/resolveReports.js');
        $this->pageHeader['bodyId'] = 'administrate';
        
        $this->view = 'administrate';
    }
}