<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use Poznavacky\Models\MessageBox;

/**
 * Kontroler starající se o výpis stránky pro správu členů třídy jejím správcům
 * @author Jan Štěch
 */
class MembersController extends SynchronousController
{
    
    /**
     * Metoda nastavující hlavičku stránky, data pro pohled a pohled
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        //Kontrola, zda třída není veřejná
        if ($_SESSION['selection']['class']->getStatus() === ClassObject::CLASS_STATUS_PUBLIC) {
            (new Logger())->warning('Zablokován pokus o přístup na stránku pro správu členů veřejné třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}',
                array(
                    'classId' => $_SESSION['selection']['class']->getId(),
                    'userId' => UserManager::getId(),
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, "Správa členů není u veřejných tříd dostupná");
            $this->redirect("menu/".$_SESSION['selection']['class']->getUrl().'/manage');
        }
        
        (new Logger())->info('Přístup na stránku pro správu členů třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}',
            array(
                'classId' => $_SESSION['selection']['class']->getId(),
                'userId' => UserManager::getId(),
                'ip' => $_SERVER['REMOTE_ADDR']
            ));
        
        self::$pageHeader['title'] = 'Správa členů';
        self::$pageHeader['description'] = 'Nástroj pro správce tříd umožňující snadnou správu členů';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/menu.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/menu.js', 'js/ajaxMediator.js', 'js/members.js');
        self::$pageHeader['bodyId'] = 'members';
        
        self::$data['members'] = $_SESSION['selection']['class']->getMembers();
    }
}

