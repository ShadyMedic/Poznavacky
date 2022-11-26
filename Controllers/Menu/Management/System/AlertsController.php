<?php

namespace Poznavacky\Controllers\Menu\Management\System;

use Poznavacky\Models\Administration;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Logger;
use Poznavacky\Models\Processors\AlertImporter;
use Poznavacky\Models\Statics\UserManager;

/**
 * Kontroler starající se o výpis stránky pro prohlížení chybových hlášení správcům služby
 * @author Jan Štěch
 */
class AlertsController extends \Poznavacky\Controllers\SynchronousController
{

    /**
     * Metoda nastavující hlavičku stránky a pohled
     * @param array $parameters Pole parametrů pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @see SynchronousController::process()
     */
    function process(array $parameters): void
    {
        $administration = new Administration();
        (new Logger())->info('Přístup na stránku pro prohlížení chybových hlášení systémovým administrátorem s ID {userId} z IP adresy {ip}',
            array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));

        $alertManager = new AlertImporter();
        self::$data['alerts'] = $administration->getAlerts();

        self::$pageHeader['title'] = 'Chybová hlášení';
        self::$pageHeader['description'] = 'Nástroj pro administrátory služby umožňující snadné prohlížení chybových hlášení.';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/administrate.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/ajaxMediator.js', 'js/administrate.js', 'js/alerts.js');
        self::$pageHeader['bodyId'] = 'alerts';
    }
}