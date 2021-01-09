<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Processors\NewClassRequester;
use Poznavacky\Models\Security\NumberAsWordCaptcha;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\MessageBox;

/** 
 * Kontroler starající se o výpis tabulky a jejího obsahu do menu stránky
 * @author Jan Štěch
 */
class MenuTableContentController extends Controller
{
    private $aquiredData;
    
    public function __construct(string $viewWithTable, $data)
    {
        $this->view = $viewWithTable;
        $this->aquiredData = $data;
    }
    
    public function process(array $parameters): void
    {
        if (gettype($this->aquiredData) === 'string')
        {
            //Vypisujeme prostou textovou hlášku
            $this->data['message'] = $this->aquiredData;
            return;
        }
        
        $this->data['table'] = $this->aquiredData;

        //Obsluha formuláře pro založení nové třídy
        if (!empty($_POST)) //Kontrola, zda právě nebyl formulář odeslán
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
            $this->data['textValue'] = @$_POST['text'];
        }
        $antispamGenerator = new NumberAsWordCaptcha();
        $antispamGenerator->generate();
        $this->data['antispamCode'] = $antispamGenerator->question;
        $this->data['specifiedEmail'] = (empty(UserManager::getEmail())) ? false : true;
    }
}

