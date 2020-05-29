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
                    $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Žádost o založení nové třídy byla úspěšně odeslána. Sledujte prosím pravidelně svou e-mailovou schránku a očekávejte naši odpověď.');
                    $this->redirect('menu');
                }
                else
                {
                    //E-mail se nepodařilo odeslat
                    $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'E-mail se nepodařilo odeslat. Zkuste to prosím později, nebo pošlete svou žádost jako issue na GitHub (viz odkaz "Nalezli jste problém" v patičce stránky)');
                }
            }
            catch (AccessDeniedException $e)
            {
                $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, $e->getMessage());
            }
            
            //Obnov data
            $this->data['emailValue'] = @$_POST['email'];
            $this->data['classNameValue'] = @$_POST['className'];
            $this->data['classCodeValue'] = @$_POST['classCode'];
            $this->data['textValue'] = @$_POST['text'];
            
        }
        
        $antispamGenerator = new NumberAsWordCaptcha();
        $antispamGenerator->generate();
        
        $this->data['antispamCode'] = $antispamGenerator->question;
        $this->data['specifiedEmail'] = (empty(UserManager::getEmail())) ? false : true;
        
        $this->pageHeader['title'] = 'Zažádat o novou třídu';
        $this->pageHeader['description'] = 'Založte si vlastní novou třídu, do které si budete moci přidávat vlastní poznávačky';
        $this->pageHeader['keywords'] = 'poznávačky, biologie, příroda, poznávačka, přírodopis, přírodověda, výuka, nová, třída, založení, zakládání, kontakt, žádost';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array();
        $this->pageHeader['bodyId'] = 'requestNewClassController';
        
        $this->view = 'requestNewClass';
    }
}

