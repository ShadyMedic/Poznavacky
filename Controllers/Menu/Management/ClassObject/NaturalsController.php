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
        self::$pageHeader['title'] = 'Správa přírodnin';
        self::$pageHeader['description'] = 'Nástroj pro správce tříd umožňující snadnou správu přírodnin';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/css.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/menu.js', 'js/ajaxMediator.js','js/naturals.js');
        self::$pageHeader['bodyId'] = 'naturals';
        self::$data['navigationBar'] = array(
            0 => array(
                'text' => self::$pageHeader['title'],
                'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage/naturals'
            )
        );
        self::$data['naturals'] = $_SESSION['selection']['class']->getNaturals();

        $this->view = 'naturals';
    }
}