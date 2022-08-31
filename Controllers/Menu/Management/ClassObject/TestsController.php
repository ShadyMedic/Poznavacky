<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Logger;
use Poznavacky\Models\Statics\UserManager;

/**
 * Kontroler starající se o výpis stránky pro správu poznávaček správcům třídy, do které patří
 * @author Jan Štěch
 */
class TestsController extends SynchronousController
{
    
    /**
     * Metoda nastavující hlavičku stránky, data pro pohled a pohled
     * @param array $parameters Pole parametrů pro zpracování kontrolerem, zde může být na první pozici URL název
     *     kotnroleru, kterému se má předat řízení
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        (new Logger())->info('Přístup na stránku pro správu poznávaček třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}',
            array(
                'classId' => $_SESSION['selection']['class']->getId(),
                'userId' => UserManager::getId(),
                'ip' => $_SERVER['REMOTE_ADDR']
            ));
        
        self::$pageHeader['title'] = 'Správa poznávaček';
        self::$pageHeader['description'] = 'Nástroj pro správce tříd umožnňující snadnou správu poznávaček';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/menu.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/ajaxMediator.js', 'js/menu.js', 'js/tests.js');
        self::$pageHeader['bodyId'] = 'tests';
        
        self::$data['baseUrl'] = 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage/tests';
        self::$data['groups'] = $_SESSION['selection']['class']->getGroups();
        self::$data['returnButtonLink'] = 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage';
    }
}

