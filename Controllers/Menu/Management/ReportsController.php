<?php
namespace Poznavacky\Controllers\Menu\Management;

use Poznavacky\Controllers\SynchronousController;

/**
 * Kontroler starající se o stránku se správou hlášení pro administrátory tříd
 * @author Jan Štěch
 */
class ReportsController extends SynchronousController
{

    /**
     * Metoda nastavující hlavičku stránky a pohled k zobrazení
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        $group = $_SESSION['selection']['group'];
        self::$data['reports'] = $group->getReports();
        self::$data['naturalsInGroup'] = $group->getNaturals();

        self::$pageHeader['title'] = 'Správa hlášení';
        self::$pageHeader['description'] = 'Nástroj pro vlastníky tříd umožňující řešení hlášení obrázků.';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/css.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/menu.js', 'js/ajaxMediator.js','js/resolveReports.js');
        self::$pageHeader['bodyId'] = 'resolve-reports';
        self::$data['navigationBar'] = array(
            0 => array(
                'text' => self::$pageHeader['title'],
                'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage/tests/'.$_SESSION['selection']['group']->getUrl().'/reports'
            )
        );
    }
}

