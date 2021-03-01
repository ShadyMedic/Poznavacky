<?php
namespace Poznavacky\Controllers\Menu\Management\System;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Administration;

/** 
 * Kontroler starající se o výpis administrační stránky správcům služby
 * @author Jan Štěch
 */
class AdministrateController extends SynchronousController
{

    /**
     * Metoda ověřující, zda má uživatel do administrace přístup a nastavující hlavičku stránky a pohled
     * @param array $parameters Pole parametrů pro zpracování kontrolerem, zde může být prvním elementem URL název dalšího kontroleru, kterému se má předat řízení
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        $aChecker = new AccessChecker();
        if (!$aChecker->checkSystemAdmin())
        {
            $this->redirect('error403');
        }

        //Zkontroluj, zda není specifikován další kontroler
        if (!empty($parameters))
        {
            //ReportActionController
            $controllerName = $this->kebabToCamelCase($parameters[0]).self::CONTROLLER_EXTENSION;
            $pathToController = $this->controllerExists($controllerName);
            if ($pathToController)
            {
                $this->controllerToCall = new $pathToController();
            }
        }

        if (!empty($this->controllerToCall))
        {
            $this->controllerToCall->process(array_slice($parameters, 1));
        }
        else
        {
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
            $this->pageHeader['jsFiles'] = array('js/generic.js','js/ajaxMediator.js','js/administrate.js');
            $this->pageHeader['bodyId'] = 'administrate';

            $this->view = 'administrate';
        }
    }
}

