<?php
namespace Poznavacky\Controllers\Menu\Management\Account;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;

/**
 * Kontroler starající se o výpis stránky s nastavením účtu
 * @author Jan Štěch
 */
class AccountSettingsController extends SynchronousController
{

    /**
     * Metoda nastavující hlavičku stránky a pohled
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        //Kontrola, zda se nejedná o demo účet
        $aChecker = new AccessChecker();
        if ($aChecker->checkDemoAccount())
        {
            $this->redirect('error403');
        }

        self::$data['userId'] = UserManager::getId();
        self::$data['userName'] = UserManager::getName();
        self::$data['userEmail'] = UserManager::getEmail();
        $otherData = UserManager::getOtherInformation();
        self::$data['addedPictures'] = $otherData['addedPictures'];
        self::$data['guessedPictures'] = $otherData['guessedPictures'];
        self::$data['karma'] = $otherData['karma'];
        self::$data['status'] = $otherData['status'];
        
        self::$pageHeader['title'] = 'Nastavení účtu';
        self::$pageHeader['description'] = 'Přizpůsobte si poznávačky podle svého gusta a podívejte se na své statistiky';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/css.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/menu.js', 'js/ajaxMediator.js','js/accountSettings.js');
        self::$pageHeader['bodyId'] = 'account-settings';
        self::$data['navigationBar'] = array(
            0 => array(
                'text' => self::$pageHeader['title'],
                'link' => 'menu/account-settings'
            )
        );

        $this->view = 'accountSettings';
    }
}

