<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Processors\NewClassRequester;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Security\NumberAsWordCaptcha;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use Poznavacky\Models\MessageBox;

/** 
 * Kontroler starající se o výpis tabulky a jejího obsahu do menu stránky
 * K tomuto kontroleru nelze přistupovat přímo (z URL adresy)
 * @author Jan Štěch
 */
class MenuTableContentController extends SynchronousController
{
    private $aquiredData;

    /**
     * Konstruktor třídy nastavující pohled a data, která do něj mají být doplněna
     * Oba parametry jsou sice dobrovolné, avšak pro správnou funkci MUSÍ být oba vyplněny
     * Možnost ponechat je nevyplněné je zachována pro případ, že se uživatel pokusí k tomuto kontroleru přistoupit přímo (a zpracování je pak zastaveno)
     * @param string $viewWithTable Název pohledu obsahujícího požadovanou tabulku
     * @param string|array $data Dvourozměrné pole obsahující data pro zobrazení v tabulce nebo řetězec, pokud zvolený pohled slouží pouze pro zobrazení jednoduché hlášky
     */
    public function __construct(string $viewWithTable = '', $data = array())
    {
        $this->view = $viewWithTable;
        $this->aquiredData = $data;
    }

    /**
     * Metoda skládající získaná data o zobrazované složce do tabulky a předávající je pohledu
     * @param array $parameters Pole parametrů, pokud je prázdné, je přístup ke kontroleru zamítnut
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        if (empty($parameters))
        {
            //Uživatel se pokouší k tomuto kontroleru přistoupit přímo
            (new Logger(true))->warning('Uživatel z IP adresy {ip} se pokusil manuálně přistoupit přímo ke kontroleru pro vypsání obsahu třídy nebo poznávačky', array('ip' => $_SERVER['REMOTE_ADDR']));
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
                (new Logger(true))->warning('Uživatel používající demo účet z IP adresy {ip} odeslal formulář pro založení nové třídy, který byl ignorován', array('ip' => $_SERVER['REMOTE_ADDR']));
                $this->redirect('error403');
            }
            
            $requester = new NewClassRequester();
            try
            {
                if ($requester->processFormData($_POST))
                {
                    (new Logger(true))->info('Uživatel s ID {userId} odeslal z IP adresy {ip} žádost o založení nové třídy s názevem {className}', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'className' => $_POST['className']));
                    $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Žádost o založení nové třídy byla úspěšně odeslána. Sledujte prosím pravidelně svou e-mailovou schránku a očekávejte naši odpověď.');
                    $this->redirect('menu');
                }
                else
                {
                    //E-mail se nepodařilo odeslat
                    (new Logger(true))->critical('Uživatel s ID {userId} přistupující do systému z IP adresy {ip} odeslal žádost o založení nové třídy se všemi náležitostmi, avšak e-mail se žádostí se webmasterovi se nepodařilo z neznámého důvodu odeslat; je možné že není možné odesílat žádné e-maily', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
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

