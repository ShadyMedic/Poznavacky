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

        $this->data['userId'] = UserManager::getId();
        $this->data['userName'] = UserManager::getName();
        $this->data['userEmail'] = UserManager::getEmail();
        $otherData = UserManager::getOtherInformation();
        $this->data['addedPictures'] = $otherData['addedPictures'];
        $this->data['guessedPictures'] = $otherData['guessedPictures'];
        $this->data['karma'] = $otherData['karma'];
        $this->data['status'] = $otherData['status'];
        
        $this->pageHeader['title'] = 'Nastavení účtu';
        $this->pageHeader['description'] = 'Přizpůsobte si poznávačky podle svého gusta a podívejte se na své statistiky';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js', 'js/menu.js', 'js/ajaxMediator.js','js/accountSettings.js');
        $this->pageHeader['bodyId'] = 'account-settings';
        $this->data['navigationBar'] = array(
            0 => array(
                'text' => $this->pageHeader['title'],
                'link' => 'menu/account-settings'
            )
        );

        $this->view = 'accountSettings';
    }
}

