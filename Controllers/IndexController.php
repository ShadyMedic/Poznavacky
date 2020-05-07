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
        //Kontrola automatického přihlášení
        if (isset($_COOKIE['instantLogin']))
        {
            try
            {
                LoginUser::processCookieLogin($_COOKIE['instantLogin']);
                
                //Přihlášení proběhlo úspěšně
                $this->redirect('menu');
            }
            catch(AccessDeniedException $e)
            {
                //Kód nebyl platný
                $_SESSION['error']['form'] = $e->getAdditionalInfo('form');
                $_SESSION['error']['message'] = $e->getMessage();
            }
        }
        
        $this->pageHeader['title'] = 'Poznávačky';
        $this->pageHeader['description'] = 'Čeká vás poznávačka z biologie? Není lepší způsob, jak se na ni naučit, než použitím této webové aplikace. Vytvořte si vlastní poznávačku, společně do ní přidávejte obrázky, učte se z nich a nechte si generovat náhodné testy.';
        $this->pageHeader['keywords'] = 'poznávačky, biologie, příroda, poznávačka, přírodopis, přírodověda, test, výuka, naučit, učit, testy, učení';
        $this->pageHeader['cssFile'] = 'css/css.css';
        $this->pageHeader['jsFile'] = 'js/index.js';
        $this->pageHeader['bodyId'] = 'index';
        
        //Práce s chybovými hláškami
        $this->data['loginError'] = '';
        $this->data['registerError'] = '';
        $this->data['passRecoveryError'] = '';
        $this->data['passRecoverySuccess'] = '';
        
        //Zkontrolovat, zda nejsou nějaké chybové nebo úspěchové hlášky k zobrazení
        if (isset($_SESSION['error']))
        {
            $index = $_SESSION['error']['form'].'Error';
            $this->data[$index] = $_SESSION['error']['message'];
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success']))
        {
            $index = $_SESSION['success']['form'].'Success';
            $this->data[$index] = $_SESSION['success']['message'];
            unset($_SESSION['success']);
        }
        
        //Práce s dříve vyplněnými formuláři
        $this->data['loginName'] = '';
        $this->data['loginPass'] = '';
        $this->data['registerName'] = '';
        $this->data['registerPass'] = '';
        $this->data['registerRepass'] = '';
        $this->data['registerEmail'] = '';
        $this->data['passRecoveryEmail'] = '';
        
        if (isset($_SESSION['previousAnswers']))
        {
            //Atributa name ve formulářích na index stránce je u každého pole nastavena na stejný název jako proměnná ze které se vypisuje hodnota atributy value
            $previousAnswers = unserialize($_SESSION['previousAnswers']);
            $this->data['loginName'] = @$previousAnswers['loginName'];
            $this->data['loginPass'] = @$previousAnswers['loginPass'];
            $this->data['registerName'] = @$previousAnswers['registerName'];
            $this->data['registerPass'] = @$previousAnswers['registerPass'];
            $this->data['registerRepass'] = @$previousAnswers['registerRepass'];
            $this->data['registerEmail'] = @$previousAnswers['registerEmail'];
            $this->data['passRecoveryEmail'] = @$previousAnswers['passRecoveryEmail'];
            
            unset($_SESSION['previousAnswers']);
        }
        
        $this->view = 'index';
    }
}