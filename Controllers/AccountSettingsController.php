<?php
/**
 * Kontroler starající se o výpis stránky s nastavením účtu
 * @author Jan Štěch
 */
class AccountSettingsController extends Controller
{

    /**
    * Metoda nastavující hlavičku stránky a pohled
    * @see Controller::process()
    */
    public function process(array $parameters)
    {
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
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/accountSettings.js');
        $this->pageHeader['bodyId'] = 'accountSettings';
        
        $this->view = 'accountSettings';
    }
}

