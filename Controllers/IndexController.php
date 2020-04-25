<?php
/** 
 * Kontroler starající se o vypsání úvodní stránky webu
 * @author Jan Štěch
 */
class IndexController extends Controller
{

    /**
     * TODO
     * @see Controller::process()
     */
    public function process(array $paremeters)
    {
        $this->pageHeader['title'] = 'Poznávačky';
        $this->pageHeader['description'] = 'Čeká vás poznávačka z biologie? Není lepší způsob jak se na ní naučit než použitím této webové aplikace. Přidejte si vlastní poznávačku, společně do ní přidávejte obrázky, učte se z nich a nechte si generovat náhodné testy. To vše kompletně zdarma.';
        $this->pageHeader['keywords'] = 'poznávačky, biologie, příroda';
        $this->pageHeader['cssFile'] = 'css/css.css';
        $this->pageHeader['jsFile'] = 'js/index.js';
        
        $this->view = 'index';
    }
}

