<?php

namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Models\Logger;
use Poznavacky\Models\Statics\UserManager;

/**
 * Kontroler starající se o výpis stránky pro správu obrázků zvolené přírodniny
 * @author Jan Štěch
 */
class PicturesController extends \Poznavacky\Controllers\SynchronousController
{

    /**
     * Metoda nastavující hlavičku stránky a pohled k zobrazení
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see SynchronousController::process()
     */
    function process(array $parameters): void
    {
        (new Logger())->info('Přístup na stránku pro správu obrázků přírodniny s ID {naturalId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}',
            array(
                'naturalId' => $_SESSION['selection']['natural']->getId(),
                'classId' => $_SESSION['selection']['class']->getId(),
                'userId' => UserManager::getId(),
                'ip' => $_SERVER['REMOTE_ADDR']
            ));

        $class = $_SESSION['selection']['class'];
        $natural = $_SESSION['selection']['natural'];
        self::$data['records'] = $natural->getPictures();
        self::$data['availableNaturals'] = $class->getNaturals();
        self::$data['reportView'] = false;

        self::$pageHeader['title'] = 'Správa obrázků';
        self::$pageHeader['description'] = 'Nástroj pro vlastníky tříd umožňující správu obrázků jednotlivých přírodnin.';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/menu.css');
        self::$pageHeader['jsFiles'] = array(
            'js/generic.js',
            'js/menu.js',
            'js/ajaxMediator.js',
            'js/resolveReports.js'
        );
        self::$pageHeader['bodyId'] = 'pictures';
    }
}