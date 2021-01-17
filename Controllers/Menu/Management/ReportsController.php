<?php
namespace Poznavacky\Controllers\Menu\Management;

use Poznavacky\Controllers\Controller;

/**
 * Kontroler starající se o stránku se správou hlášení pro administrátory tříd
 * @author Jan Štěch
 */
class ReportsController extends Controller
{

    /**
     * Metoda nastavující hlavičku stránky a pohled k zobrazení
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        $group = $_SESSION['selection']['group'];
        $this->data['reports'] = $group->getReports();
        $this->data['naturalsInGroup'] = $group->getNaturals();
        $this->data['returnButtonLink'] = 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage/tests';
        
        $this->pageHeader['title'] = 'Správa hlášení';
        $this->pageHeader['description'] = 'Nástroj pro vlastníky tříd umožňující řešení hlášení obrázků.';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js', 'js/menu.js', 'js/ajaxMediator.js','js/resolveReports.js');
        $this->pageHeader['bodyId'] = 'resolve-reports';
        $this->data['navigationBar'] = array(
            0 => array(
                'text' => $this->pageHeader['title'],
                'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage/tests/'.$_SESSION['selection']['group']->getUrl().'/reports'
            )
        );
        
        $this->view = 'reports';
    }
}

