<?php
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
    public function process(array $parameters)
    {
        $group = $_SESSION['selection']['group'];
        $this->data['reports'] = $group->getReports();
        $this->data['naturalsInGroup'] = $group->getNaturals();
        $this->data['returnButtonLink'] = 'menu/'.$_SESSION['selection']['class']->getName().'/manage/tests';
        
        $this->pageHeader['title'] = 'Řešit hlášení';
        $this->pageHeader['description'] = 'Nástroj pro vlastníky tříd umožňující řešení hlášení obrázků.';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/resolveReports.js');
        $this->pageHeader['bodyId'] = 'resolveReports';
        
        $this->view = 'reports';
    }
}

