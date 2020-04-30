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
        $this->data['passRecoveryError'] = '';
        $this->data['passRecoverySuccess'] = '';
        
        //Zkontrolovat, zda nejsou nějaké chybové nebo úspěchové hlášky k zobrazení
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
        if (isset($_COOKIE['successForm']))
        {
            $index = $_COOKIE['successForm'].'Success';
            $this->data[$index] = $_COOKIE['successMessage'];
            
            //Vymazání cookies
            unset($_COOKIE['successForm']);
            unset($_COOKIE['successMessage']);
            setcookie('successForm', null, -1);
            setcookie('successMessage', null, -1);
        }
        
        //Práce s dříve vyplněnými formuláři
        $this->data['loginName'] = '';
        $this->data['loginPass'] = '';
        $this->data['registerName'] = '';
        $this->data['registerPass'] = '';
        $this->data['registerRepass'] = '';
        $this->data['registerEmail'] = '';
        $this->data['passRecoveryEmail'] = '';
        
        if (isset($_COOKIE['previousAnswers']))
        {
            //Atributa name ve formulářích na index stránce je u každého pole nastavena na stejný název jako proměnná ze které se vypisuje hodnota atributy value
            $previousAnswers = unserialize($_COOKIE['previousAnswers']);
            $this->data['loginName'] = @$previousAnswers['loginName'];
            $this->data['loginPass'] = @$previousAnswers['loginPass'];
            $this->data['registerName'] = @$previousAnswers['registerName'];
            $this->data['registerPass'] = @$previousAnswers['registerPass'];
            $this->data['registerRepass'] = @$previousAnswers['registerRepass'];
            $this->data['registerEmail'] = @$previousAnswers['registerEmail'];
            $this->data['passRecoveryEmail'] = @$previousAnswers['passRecoveryEmail'];
            
            unset($_COOKIE['previousAnswers']);
            setcookie('previousAnswers', null, -1);
        }
        
        $this->view = 'index';
    }
}