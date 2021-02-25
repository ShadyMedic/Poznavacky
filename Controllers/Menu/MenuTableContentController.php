<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Processors\NewClassRequester;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Security\NumberAsWordCaptcha;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\MessageBox;

/** 
 * Kontroler starající se o výpis tabulky a jejího obsahu do menu stránky
 * K tomuto kontroleru nelze přistupovat přímo (z URL adresy)
 * @author Jan Štěch
 */
class MenuTableContentController extends Controller
{
    private $aquiredData;

    /**
     * Konstruktor třídy nastavující pohled a data, která do něj mají být doplněna
     * Oba parametry jsou sice dobrovolné, avšak pro správnou funkci MUSÍ být oba vyplněny
     * Možnost ponechat je nevyplněné je zachována pro případ, že se uživatel pokusí k tomuto kontroleru přistoupit přímo (a zpracování je pak zastaveno)
     * @param string $viewWithTable Název pohledu obsahujícího požadovanou tabulku
     * @param array $data Dvourozměrné pole obsahující data pro zobrazení v tabulce
     */
    public function __construct(string $viewWithTable = '', array $data = array())
    {
        $this->view = $viewWithTable;
        $this->aquiredData = $data;
    }

    /**
     * Metoda skládající získaná data o zobrazované složce do tabulky a předávající je pohledu
     * @param array $parameters Pole parametrů, pokud je prázdné, je přístup ke kontroleru zamítnut
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        if (empty($parameters))
        {
            //Uživatel se pokouší k tomuto kontroleru přistoupit přímo
            $this->redirect('menu');
        }

        if (gettype($this->aquiredData) === 'string')
        {
            //Vypisujeme prostou textovou hlášku
            $this->data['message'] = $this->aquiredData;
            return;
        }
        
        $this->data['table'] = $this->aquiredData;
		$this->data['invitations'] = UserManager::getUser()->getActiveInvitations();
		$this->data['invitationsCount'] = count($this->data['invitations']);
        $checker = new AccessChecker();
        $this->data['demoVersion'] = $checker->checkDemoAccount();

        //Obsluha formuláře pro založení nové třídy
        if (!empty($_POST)) //Kontrola, zda právě nebyl formulář odeslán
        {
            //Kontrola, zda se nejedná o demo účet
            $aChecker = new AccessChecker();
            if ($aChecker->checkDemoAccount())
            {
                $this->redirect('error403');
            }
            
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

            $this->data['displayNewClassForm'] = true;
        }
        else { $this->data['displayNewClassForm'] = false; }

        $antispamGenerator = new NumberAsWordCaptcha();
        $antispamGenerator->generate();
        $this->data['antispamCode'] = $antispamGenerator->question;
        $this->data['specifiedEmail'] = (empty(UserManager::getEmail())) ? false : true;
    }
}

