<?php

namespace Poznavacky\Controllers\Menu\Management\System;

use Poznavacky\Models\Administration;
use Poznavacky\Models\Logger;
use Poznavacky\Models\Statics\UserManager;

class AlertsController extends \Poznavacky\Controllers\SynchronousController
{

    /**
     * @inheritDoc
     */
    function process(array $parameters): void
    {
        $administration = new Administration();
        (new Logger())->info('Přístup na stránku pro prohlížení chybových hlášení systémovým administrátorem s ID {userId} z IP adresy {ip}',
            array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));

        // TODO: Získej data pro pohled

        self::$pageHeader['title'] = 'Chybová hlášení';
        self::$pageHeader['description'] = 'Nástroj pro administrátory služby umožňující snadné prohlížení chybových hlášení.';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/administrate.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/ajaxMediator.js', 'js/administrate.js', 'js/alerts.js');
        self::$pageHeader['bodyId'] = 'alerts';
    }
}