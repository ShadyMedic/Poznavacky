<?php
namespace Poznavacky\Controllers\Menu\Management\System;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Administration;

/** 
 * Kontroler starající se o výpis administrační stránky správcům služby
 * @author Jan Štěch
 */
class AdministrateController extends SynchronousController
{

    /**
     * Metoda nastavující hlavičku stránky a pohled
     * @param array $parameters Pole parametrů pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @throws DatabaseException
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

