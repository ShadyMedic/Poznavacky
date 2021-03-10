<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\SynchronousController;

/**
 * Kontroler starající se o výpis stránky pro administraci třídy jejím správcům
 * @author Jan Štěch
 */
class ManageController extends SynchronousController
{
    /**
     * Metoda ověřující, zda má uživatel do správy třídy přístup (je její správce nebo administrátor systému) a nastavující hlavičku stránky a pohled
     * @param array $parameters Pole parametrů pro zpracování kontrolerem, zde může být prvním prvkem URL název kontroleru, kterému se má předat řízení
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        self::$pageHeader['title'] = 'Správa třídy';
        self::$pageHeader['description'] = 'Nástroj pro správce tříd umožňující snadnou správu třídy';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/css.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/menu.js', 'js/ajaxMediator.js','js/manage.js');
        self::$pageHeader['bodyId'] = 'manage';

        self::$data['baseUrl'] = 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage';
        self::$data['classId'] = $_SESSION['selection']['class']->getId();
        self::$data['className'] = $_SESSION['selection']['class']->getName();
        self::$data['classStatus'] = $_SESSION['selection']['class']->getStatus();
        self::$data['classCode'] = $_SESSION['selection']['class']->getCode();
    }
}

