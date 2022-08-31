<?php
namespace Poznavacky\Controllers\Menu\Management\System;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Administration;
use Poznavacky\Models\Logger;

/**
 * Kontroler starající se o výpis stránky pro ovládání databáze správcům služby
 * @author Jan Štěch
 */
class DatabaseController extends SynchronousController
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
        (new Logger())->info('Přístup na stránku pro ovládání databáze systémovým administrátorem s ID {userId} z IP adresy {ip}',
            array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));

        self::$pageHeader['title'] = 'Ovládání databáze';
        self::$pageHeader['description'] = 'Nástroj pro administrátory služby umožňující přímé ovládání databáze.';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/administrate.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/ajaxMediator.js', 'js/administrate.js', 'js/database.js');
        self::$pageHeader['bodyId'] = 'database';
    }
}

