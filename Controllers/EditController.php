<?php
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
    public function process(array $parameters)
    {
        //TODO - získat data pro pohled
        
        $this->pageHeader['title'] = 'Upravit poznávačku';
        $this->pageHeader['description'] = 'Nástroj pro vlastníky tříd umožňující snadnou úpravu poznávaček.';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/editGroup.js');
        $this->pageHeader['bodyId'] = 'editGroup';
        
        $this->view = 'editGroup';
    }
}

