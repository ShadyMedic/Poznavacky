<?php
namespace Poznavacky\Controllers\Menu\Study\AddPictures;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;

/**
 * Kontroler starající se o výpis stránky pro přidání obrázků
 * @author Jan Štěch
 */
class AddPicturesController extends SynchronousController
{
    
    /**
     * Metoda nastavující hlavičku stránky a pohled
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        self::$pageHeader['title'] = 'Přidat obrázky';
        self::$pageHeader['description'] = 'Přidávejte obrázky do své poznávačky, aby se z nich mohli učit všichni členové třídy';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/menu.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/ajaxMediator.js', 'js/addPictures.js', 'js/menu.js');
        self::$pageHeader['bodyId'] = 'add-pictures';
        
        $aChecker = new AccessChecker();
        if (!$aChecker->checkPart()) {
            self::$data['naturals'] = $_SESSION['selection']['group']->getNaturals();
            (new Logger())->info('Přístup na stránku pro přidávání obrázků do všech částí poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}',
                array(
                    'groupId' => $_SESSION['selection']['group']->getId(),
                    'classId' => $_SESSION['selection']['class']->getId(),
                    'userId' => UserManager::getId(),
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
        } else {
            self::$data['naturals'] = $_SESSION['selection']['part']->getNaturals();
            (new Logger())->info('Přístup na stránku pro přidávání obrázků do části s ID {partId} patřící do poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}',
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

