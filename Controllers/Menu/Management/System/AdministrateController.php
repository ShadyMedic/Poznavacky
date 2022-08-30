<?php
namespace Poznavacky\Controllers\Menu\Management\System;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Administration;
use Poznavacky\Models\Logger;

/**
 * Kontroler starající se o výpis domovské administrační stránky správcům služby
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
        (new Logger(true))->info('Přístup na stránku pro správu systému systémovým administrátorem s ID {userId} z IP adresy {ip}',
            array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
        
        self::$data['userCount'] = $administration->getUserCount();
        self::$data['classesCount'] = $administration->getClassCount();
        self::$data['reportCount'] = $administration->getAdminReportCount();
        self::$data['userNameChangeRequestsCount'] = $administration->getUserNameChangeRequestCount();
        self::$data['classNameChangeRequestsCount'] = $administration->getClassNameChangeRequestCount();

        self::$pageHeader['title'] = 'Správa služby';
        self::$pageHeader['description'] = 'Nástroj pro administrátory služby umožňující snadnou správu různých součástí systému.';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/administrate.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/ajaxMediator.js', 'js/administrate.js');
        self::$pageHeader['bodyId'] = 'administrate';
    }
}

