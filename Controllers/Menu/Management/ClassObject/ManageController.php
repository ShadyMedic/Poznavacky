<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Logger;
use Poznavacky\Models\Statics\UserManager;

/**
 * Kontroler starající se o výpis stránky pro administraci třídy jejím správcům
 * @author Jan Štěch
 */
class ManageController extends SynchronousController
{
    /**
     * Metoda nastavující hlavičku stránky a pohled
     * @param array $parameters Pole parametrů pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        (new Logger(true))->info('Přístup na stránku pro správu třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}',
            array(
                'classId' => $_SESSION['selection']['class']->getId(),
                'userId' => UserManager::getId(),
                'ip' => $_SERVER['REMOTE_ADDR']
            ));
        
        self::$pageHeader['title'] = 'Správa třídy';
        self::$pageHeader['description'] = 'Nástroj pro správce tříd umožňující snadnou správu třídy';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/css.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/menu.js', 'js/ajaxMediator.js', 'js/manage.js');
        self::$pageHeader['bodyId'] = 'manage';
        
        self::$data['baseUrl'] = 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage';
        self::$data['classId'] = $_SESSION['selection']['class']->getId();
        self::$data['className'] = $_SESSION['selection']['class']->getName();
        self::$data['classStatus'] = $_SESSION['selection']['class']->getStatus();
        self::$data['classCode'] = $_SESSION['selection']['class']->getCode();
    }
}

