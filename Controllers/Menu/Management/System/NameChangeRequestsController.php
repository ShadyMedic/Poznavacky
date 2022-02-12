<?php
namespace Poznavacky\Controllers\Menu\Management\System;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Administration;
use Poznavacky\Models\Logger;

/**
 * Kontroler starající se o výpis stránky pro správu žádostí o změnu jména správcům služby
 * @author Jan Štěch
 */
class NameChangeRequestsController extends SynchronousController
{

    /**
     * Metoda nastavující hlavičku stránky a pohled
     * @param array $parameters Pole parametrů pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @throws DatabaseException
     * @see SynchronousController::process()
     */
    function process(array $parameters): void
    {
        $administration = new Administration();
        (new Logger(true))->info('Přístup na stránku pro správu žádostí o změnu jména systémovým administrátorem s ID {userId} z IP adresy {ip}',
            array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));

        self::$data['userNameChangeRequests'] = $administration->getUserNameChangeRequests();
        self::$data['classNameChangeRequests'] = $administration->getClassNameChangeRequests();

        self::$pageHeader['title'] = 'Správa změn jmen';
        self::$pageHeader['description'] = 'Nástroj pro administrátory služby umožňující snadnou správu žádostí o změnu jména.';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/private.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/ajaxMediator.js', 'js/administrate.js', 'js/nameChangeRequests.js');
        self::$pageHeader['bodyId'] = 'name-change-requests';
    }
}

