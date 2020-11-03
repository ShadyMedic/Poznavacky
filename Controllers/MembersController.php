<?php

/** 
 * Kontroler starající se o výpis stránky pro správu členů třídy jejím správcům
 * @author Jan Štěch
 */
class MembersController extends Controller
{

    /**
     * Metoda nastavující hlavičku stránky, data pro pohled a pohled
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        $this->pageHeader['title'] = 'Správa členů';
        $this->pageHeader['description'] = 'Nástroj pro správce tříd umožňující snadnou správu členů';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/members.js');
        $this->pageHeader['bodyId'] = 'members';
        
        $this->data['members'] = $_SESSION['selection']['class']->getMembers(false); //false zajistí, že se nezobrazí právě přihlášený uživatel
        $this->data['returnButtonLink'] = 'menu/'.$_SESSION['selection']['class']->getName().'/manage';
        
        $this->view = 'members';
    }
}