<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Logger;
use Poznavacky\Models\Statics\UserManager;

/**
 * Kontroler starající se o výpis stránky pro správu přírodnin třídy jejím správcům
 * @author Jan Štěch
 */
class NaturalsController extends SynchronousController
{
    
    /**
     * Metoda nastavující hlavičku stránky, data pro pohled a pohled
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @see SynchronousController::process()
     */
    function process(array $parameters): void
    {
        (new Logger(true))->info('Přístup na stránku pro správu přírodnin třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}',
            array(
                'classId' => $_SESSION['selection']['class']->getId(),
                'userId' => UserManager::getId(),
                'ip' => $_SERVER['REMOTE_ADDR']
            ));
        
        self::$pageHeader['title'] = 'Správa přírodnin';
        self::$pageHeader['description'] = 'Nástroj pro správce tříd umožňující snadnou správu přírodnin';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/css.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/menu.js', 'js/ajaxMediator.js', 'js/naturals.js');
        self::$pageHeader['bodyId'] = 'naturals';
        
        self::$data['naturals'] = $_SESSION['selection']['class']->getNaturals();
    }
}

