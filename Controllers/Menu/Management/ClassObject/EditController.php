<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\DatabaseItems\Group;
use Poznavacky\Models\DatabaseItems\Natural;

/**
 * Kontroler starající se o stránku umožňující úpravu poznávaček pro administrátory tříd
 * @author Jan Štěch
 */
class EditController extends Controller
{

    /**
     * Metoda nastavující hlavičku stránky a pohled k zobrazení
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {        
        $this->pageHeader['title'] = 'Upravit poznávačku';
        $this->pageHeader['description'] = 'Nástroj pro vlastníky tříd umožňující snadnou úpravu poznávaček.';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/edit.js');
        $this->pageHeader['bodyId'] = 'editGroup';
        
        $this->data['returnButtonLink'] = 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage/tests';
        
        //Metoda získání URL poznávaček a jmen přírodnin napsaná podle jednoho komentáře pod touto odpovědí na StackOverflow: https://stackoverflow.com/a/1119029/14011077
        $this->data['groupList'] = array_map(function (Group $group): string {return $group->getUrl(); }, $_SESSION['selection']['class']->getGroups());
        $this->data['naturalList'] = array_map(function (Natural $natural): string {return $natural->getName(); }, $_SESSION['selection']['class']->getNaturals());
        
        $this->view = 'edit';
    }
}

