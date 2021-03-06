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
        $administration = new Administration();

        self::$data['loggedAdminName'] = UserManager::getName();

        self::$data['users'] = $administration->getAllUsers(false);
        self::$data['classes'] = $administration->getAllClasses();
        self::$data['reports'] = $administration->getAdminReports();
        self::$data['userNameChangeRequests'] = $administration->getUserNameChangeRequests();
        self::$data['classNameChangeRequests'] = $administration->getClassNameChangeRequests();

        self::$pageHeader['title'] = 'Správa služby';
        self::$pageHeader['description'] = 'Nástroj pro administrátory služby umožňující snadnou správu různých součástí systému.';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/private.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js','js/ajaxMediator.js','js/administrate.js');
        self::$pageHeader['bodyId'] = 'administrate';
    }
}

