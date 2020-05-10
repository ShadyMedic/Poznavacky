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
        $this->pageHeader['title'] = 'Nastavení účtu';
        $this->pageHeader['description'] = 'Přizpůsobte si poznávačky podle svého gusta a podívejte se na své statistiky';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFile'] = 'css/css.css';
        $this->pageHeader['jsFile'] = 'js/accountSettings.js';
        $this->pageHeader['bodyId'] = 'accountSettings';
        
        $this->view = 'accountSettings';
    }
}

