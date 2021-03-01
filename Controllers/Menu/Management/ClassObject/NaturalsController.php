<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\SynchronousController;

/**
 * Kontroler starající se o výpis stránky pro správu přírodnin třídy jejím správcům
 * @author Jan Štěch
 */
class NaturalsController extends SynchronousController
{

    /**
     * Metoda nastavující hlavičku stránky, data pro pohled a pohled
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see SynchronousController::process()
     */
    function process(array $parameters): void
    {
        $this->pageHeader['title'] = 'Správa přírodnin';
        $this->pageHeader['description'] = 'Nástroj pro správce tříd umožňující snadnou správu přírodnin';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js', 'js/menu.js', 'js/ajaxMediator.js','js/naturals.js');
        $this->pageHeader['bodyId'] = 'naturals';
        $this->data['navigationBar'] = array(
            0 => array(
                'text' => $this->pageHeader['title'],
                'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage/naturals'
            )
        );
        $this->data['naturals'] = $_SESSION['selection']['class']->getNaturals();

        $this->view = 'naturals';
    }
}