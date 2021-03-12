<?php
namespace Poznavacky\Controllers\Menu;

use PHPMailer\PHPMailer\Exception;
use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Logger;
use Poznavacky\Models\Processors\NewClassRequester;
use Poznavacky\Models\Security\NumberAsWordCaptcha;
use Poznavacky\Models\Statics\UserManager;

class RequestNewClassController extends AjaxController
{

    /**
     * Metoda načítající data odeslaná z formuláře pro založení třídy AJAX POST požadavkem, kontrolující je a případně odesílající e-mail webmasterovi
     * @param array $parameters Pole parametrů pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException
     * @throws DatabaseException
     */
    function process(array $parameters): void
    {
        $requester = new NewClassRequester();
        $response = null;
        try
        {
            if ($requester->processFormData($_POST))
            {
                (new Logger(true))->info('Uživatel s ID {userId} odeslal z IP adresy {ip} žádost o založení nové třídy s názevem {className}', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'className' => $_POST['className']));
                $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Žádost o založení nové třídy byla úspěšně odeslána. Sledujte prosím pravidelně svou e-mailovou schránku a očekávejte naši odpověď.');
            }
            else
            {
                throw new Exception();
            }
        }
        catch (AccessDeniedException $e)
        {
            //Neplatné údaje
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage(), array('newCaptcha' => $this->renewCaptcha()));
        }
        catch (Exception $e)
        {
            //E-mail se nepodařilo odeslat
            (new Logger(true))->critical('Uživatel s ID {userId} přistupující do systému z IP adresy {ip} odeslal žádost o založení nové třídy se všemi náležitostmi, avšak e-mail se žádostí se webmasterovi se nepodařilo z neznámého důvodu odeslat; je možné že není možné odesílat žádné e-maily', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, 'E-mail se nepodařilo odeslat. Zkuste to prosím později, nebo pošlete svou žádost jako issue na GitHub (viz odkaz "Nalezli jste problém" v patičce stránky)', array('newCaptcha' => $this->renewCaptcha()));
        }

        echo $response->getResponseString();
    }

    /**
     * Metoda obnovující otázku pro ochranu proti robotům
     * Nová odpověď je uložena do $_SESSION
     * @return string Obnovená otázka
     */
    private function renewCaptcha(): string
    {
        $antispamGenerator = new NumberAsWordCaptcha();
        $antispamGenerator->generate();
        return $antispamGenerator->question;
    }
}

