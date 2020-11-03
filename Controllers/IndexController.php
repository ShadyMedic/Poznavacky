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
    public function process(array $paremeters): void
    {
        //Kontrola, zda již uživatel není přihlášen
        $aChecker = new AccessChecker();
        if ($aChecker->checkUser())
        {
            //Uživatel je již přihlášen
            $this->redirect('menu');
        }
        
        //Kontrola automatického přihlášení
        if (isset($_COOKIE['instantLogin']))
        {
            try
            {
                $userLogger = new LoginUser();
                $userLogger->processCookieLogin($_COOKIE['instantLogin']);
                
                //Přihlášení proběhlo úspěšně
                $this->redirect('menu');
            }
            catch(AccessDeniedException $e)
            {
                //Kód nebyl platný
                $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, $e->getMessage());
                
                //Vymaž cookie s neplatným kódem
                setcookie('instantLogin', null, -1);
                unset($_COOKIE['instantLogin']);
            }
        }
        
        $this->pageHeader['title'] = 'Poznávačky';
        $this->pageHeader['description'] = 'Čeká vás poznávačka z biologie? Není lepší způsob, jak se na ni naučit, než použitím této webové aplikace. Vytvořte si vlastní poznávačku, společně do ní přidávejte obrázky, učte se z nich a nechte si generovat náhodné testy.';
        $this->pageHeader['keywords'] = 'poznávačky, biologie, příroda, poznávačka, přírodopis, přírodověda, test, výuka, naučit, učit, testy, učení';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/index.js');
        $this->pageHeader['bodyId'] = 'index';
        
        $this->view = 'index';
    }
}