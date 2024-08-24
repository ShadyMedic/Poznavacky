<?php
namespace Poznavacky\Controllers\Menu\Management;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Logger;
use Poznavacky\Models\Statics\UserManager;

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
        (new Logger())->info('Přístup na stránku pro správu hlášení ve třídě s ID {classId} uživatelem s ID {userId} z IP adresy {ip}',
            array(
                'classId' => $_SESSION['selection']['class']->getId(),
                'userId' => UserManager::getId(),
                'ip' => $_SERVER['REMOTE_ADDR']
            ));
        
        $class = $_SESSION['selection']['class'];
        self::$data['reports'] = $class->getReports();
        self::$data['naturalsInClass'] = $class->getNaturals();
        
        self::$pageHeader['title'] = 'Správa hlášení';
        self::$pageHeader['description'] = 'Nástroj pro vlastníky tříd umožňující řešení hlášení obrázků.';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/menu.css');
        self::$pageHeader['jsFiles'] = array(
            'js/generic.js',
            'js/menu.js',
            'js/ajaxMediator.js',
            'js/resolveReports.js'
        );
        self::$pageHeader['bodyId'] = 'resolve-reports';
    }
}

