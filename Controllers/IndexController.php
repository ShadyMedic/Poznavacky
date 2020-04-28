<?php
/** 
 * Kontroler starající se o vypsání úvodní stránky webu
 * @author Jan Štěch
 */
class IndexController extends Controller
{

    /**
     * Metoda nastavující hlavičku stránky a pohled k zobrazení
     * @see Controller::process()
     */
    public function process(array $paremeters)
    {
        $this->pageHeader['title'] = 'Poznávačky';
        $this->pageHeader['description'] = 'Čeká vás poznávačka z biologie? Není lepší způsob jak se na ní naučit než použitím této webové aplikace. Přidejte si vlastní poznávačku, společně do ní přidávejte obrázky, učte se z nich a nechte si generovat náhodné testy. To vše kompletně zdarma.';
        $this->pageHeader['keywords'] = 'poznávačky, biologie, příroda';
        $this->pageHeader['cssFile'] = 'css/css.css';
        $this->pageHeader['jsFile'] = 'js/index.js';
        $this->pageHeader['bodyId'] = 'index';
        
        //Práce s chybovými hláškami
        $this->data['loginError'] = '';
        $this->data['registerError'] = '';
        $this->fata['passRecoveryError'] = '';
        
        //Zkontrolovat, zda nejsou nějaké chybové hlášky k zobrazení
        if (isset($_COOKIE['errorForm']))
        {
            $index = $_COOKIE['errorForm'].'Error';
            $this->data[$index] = $_COOKIE['errorMessage'];
            
            //Vymazání cookies
            unset($_COOKIE['errorForm']);
            unset($_COOKIE['errorMessage']);
            setcookie('errorForm', null, -1);
            setcookie('errorMessage', null, -1);
        }
        
        $this->view = 'index';
    }
}