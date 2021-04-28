<?php
namespace Poznavacky\Controllers\Menu\Study\Test;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;

/**
 * Kontroler starající se o výpis stránky pro testování
 * @author Jan Štěch
 */
class TestController extends SynchronousController
{
    /**
     * Metoda nastavující hlavičku stránky a pohled
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        self::$pageHeader['title'] = 'Vyzkoušet se';
        self::$pageHeader['description'] = 'Vyzkoušejte si, jak dobře znáte přírodniny v poznávačce pomocí náhodného testování';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/menu.css');
        self::$pageHeader['jsFiles'] = array(
            'js/generic.js',
            'js/ajaxMediator.js',
            'js/test.js',
            'js/reportForm.js',
            'js/menu.js'
        );
        self::$pageHeader['bodyId'] = 'test';
        
        $aChecker = new AccessChecker();
        if (!$aChecker->checkPart()) {
            (new Logger(true))->info('Přístup na stránku pro zkoušení ze všech částí poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}',
                array(
                    'groupId' => $_SESSION['selection']['group']->getId(),
                    'classId' => $_SESSION['selection']['class']->getId(),
                    'userId' => UserManager::getId(),
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
        } else {
            (new Logger(true))->info('Přístup na stránku pro zkoušení z části s ID {partId} patřící do poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}',
                array(
                    'partId' => $_SESSION['selection']['part']->getId(),
                    'groupId' => $_SESSION['selection']['group']->getId(),
                    'classId' => $_SESSION['selection']['class']->getId(),
                    'userId' => UserManager::getId(),
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
        }
    }
}

