<?php
/**
 * Kontroler starající se o stránku s formulářem pro zažádání o založení nové třídy
 * @author Jan Štěch
 */
class RequestNewClassController extends Controller
{
    /**
     * Metoda nastavující hlavičky a pohled
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        //Kontrola, zda právě nebyl formulář odeslán
        if (!empty($_POST))
        {
            $requester = new NewClassRequester();
            try
            {
                if ($requester->processFormData($_POST))
                {
                    //TODO - přidat zprávu o úspěšném odeslání žádosti
                    $this->redirect('menu');
                }
                else
                {
                    //E-mail se nepodařilo odeslat
                    throw new AccessDeniedException('E-mail se nepodařilo odeslat. Zkuste to prosím později, nebo pošlete svou žádost jako issue na GitHub (viz odkaz "Nalezli jste problém" v patičce stránky)');
                }
            }
            catch (AccessDeniedException $e)
            {
                $this->data['errorMessage'] = $e->getMessage();
                
                $this->data['emailValue'] = @$_POST['email'];
                $this->data['classNameValue'] = @$_POST['className'];
                $this->data['classCodeValue'] = @$_POST['classCode'];
                $this->data['textValue'] = @$_POST['text'];
            }
            
        }
        
        $antispamGenerator = new NumberAsWordCaptcha();
        $antispamGenerator->generate();
        
        $this->data['antispamCode'] = $antispamGenerator->question;
        $this->data['specifiedEmail'] = (empty(UserManager::getEmail())) ? false : true;
        
        $this->pageHeader['title'] = 'Zažádat o novou třídu';
        $this->pageHeader['description'] = 'Založte si vlastní novou třídu, do které si budete moci přidávat vlastní poznávačky';
        $this->pageHeader['keywords'] = 'poznávačky, biologie, příroda, poznávačka, přírodopis, přírodověda, výuka, nová, třída, založení, zakládání, kontakt, žádost';
        $this->pageHeader['cssFile'] = 'css/css.css';
        #$this->pageHeader['jsFile'] = '';
        $this->pageHeader['bodyId'] = 'requestNewClassController';
        
        $this->view = 'requestNewClass';
    }
}

