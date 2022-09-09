<?php
namespace Poznavacky\Controllers\Menu\Management\System;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Administration;
use Poznavacky\Models\Logger;

/**
 * Kontroler starající se o výpis stránky pro správu tříd správcům služby
 * @author Jan Štěch
 */
class ClassesController extends SynchronousController
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
        (new Logger())->info('Přístup na stránku pro správu tříd systémovým administrátorem s ID {userId} z IP adresy {ip}',
            array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));

        self::$data['classes'] = $administration->getAllClasses();

        self::$pageHeader['title'] = 'Správa tříd';
        self::$pageHeader['description'] = 'Nástroj pro administrátory služby umožňující snadnou správu tříd.';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/administrate.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/ajaxMediator.js', 'js/administrate.js', 'js/classes.js');
        self::$pageHeader['bodyId'] = 'classes';
    }
}

