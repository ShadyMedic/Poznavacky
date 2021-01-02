<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\DatabaseItems\Group;
use Poznavacky\Models\DatabaseItems\Natural;
use Poznavacky\Models\DatabaseItems\Part;

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
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/ajaxMediator.js','js/edit.js');
        $this->pageHeader['bodyId'] = 'editGroup';
        $this->data['navigationBar'] = array(
            0 => array(
                'text' => $this->pageHeader['title'],
                'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage/members/tests/'.$_SESSION['selection']['class']->getUrl().'/edit'
            )
        );
        $this->data['returnButtonLink'] = 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage/tests';

        //Metoda získání URL poznávaček a jmen přírodnin napsaná podle jednoho komentáře pod touto odpovědí na StackOverflow: https://stackoverflow.com/a/1119029/14011077
        $this->data['groupList'] = array_map(function (Group $group): string { return $group->getUrl(); }, $_SESSION['selection']['class']->getGroups());
        $this->data['naturalList'] = array_map(function (Natural $natural): string { return mb_strtolower($natural->getName()); }, $_SESSION['selection']['class']->getNaturals());
        $this->data['groupName'] = $_SESSION['selection']['group']->getName();
        $this->data['groupUrl'] = $_SESSION['selection']['group']->getUrl();
        //Seznam objektu částí vrať ořezaný od všech nepotřebných údajů
        //Kvůli rekurzi (části odkazují na poznávačky, ty na třídu a vše funguje i zpětně) by se muselo proti XSS ošetřovat ohromné množství proměnných
        $this->data['parts'] = array_map(function (Part $part): Part
        {
            $strippedPart = new Part(false);
            $strippedNaturals = array_map(function (Natural $natural)
            {
                $strippedNatural = new Natural(false);
                $strippedNatural->initialize($natural->getName()); //Potřebujeme jenom název přírodniny
                return $strippedNatural;
            }, $part->getNaturals());
            $strippedPart->initialize($part->getName(), $part->getUrl(), null, $strippedNaturals);   //Potřebujeme jenom název a URL části a seznam přírodnin
            return $strippedPart;
        }, $_SESSION['selection']['group']->getParts());

        $this->view = 'edit';
    }
}

