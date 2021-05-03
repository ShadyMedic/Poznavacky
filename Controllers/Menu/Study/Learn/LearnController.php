<?php
namespace Poznavacky\Controllers\Menu\Study\Learn;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;

/**
 * Kontroler starající se o výpis stránky pro učení se
 * @author Jan Štěch
 */
class LearnController extends SynchronousController
{
    
    /**
     * Metoda nastavující hlavičku stránky a pohled
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        self::$pageHeader['title'] = 'Učit se';
        self::$pageHeader['description'] = 'Učte se na poznávačku podle svého vlastního tempa';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/menu.css');
        self::$pageHeader['jsFiles'] = array(
            'js/generic.js',
            'js/ajaxMediator.js',
            'js/learn.js',
            'js/reportForm.js',
            'js/menu.js'
        );
        self::$pageHeader['bodyId'] = 'learn';
        
        $aChecker = new AccessChecker();
        if (!$aChecker->checkPart()) {
            $naturals = $_SESSION['selection']['group']->getNaturals();
        } else {
            $naturals = $_SESSION['selection']['part']->getNaturals();
        }
        
        //Vyfiltruj přírodniny bez obrázků
        $filteredNaturals = array();
        foreach ($naturals as $natural) {
            if ($natural->getPicturesCount() > 0) {
                $filteredNaturals[] = $natural;
            }
        }
        self::$data['naturals'] = $filteredNaturals;
        
        if (!$aChecker->checkPart()) {
            (new Logger(true))->info('Přístup na stránku pro učení všech částí poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}',
                array(
                    'groupId' => $_SESSION['selection']['group']->getId(),
                    'classId' => $_SESSION['selection']['class']->getId(),
                    'userId' => UserManager::getId(),
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
        } else {
            (new Logger(true))->info('Přístup na stránku pro učení části s ID {partId} patřící do poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}',
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

