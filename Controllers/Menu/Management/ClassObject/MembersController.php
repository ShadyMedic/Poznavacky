<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\DatabaseItems\ClassObject;
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
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        //Kontrola, zda třída není veřejná
        if ($_SESSION['selection']['class']->getStatus() === ClassObject::CLASS_STATUS_PUBLIC)
        {
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, "Správa členů není u veřejných tříd dostupná");
            $this->redirect("menu/".$_SESSION['selection']['class']->getUrl().'/manage');
        }

        self::$pageHeader['title'] = 'Správa členů';
        self::$pageHeader['description'] = 'Nástroj pro správce tříd umožňující snadnou správu členů';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/css.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/menu.js', 'js/ajaxMediator.js','js/members.js');
        self::$pageHeader['bodyId'] = 'members';

        self::$data['members'] = $_SESSION['selection']['class']->getMembers(false); //false zajistí, že se nezobrazí právě přihlášený uživatel
    }
}

