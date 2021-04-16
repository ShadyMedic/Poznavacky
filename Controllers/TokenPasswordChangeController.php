<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\TokenPasswordChanger;
use Poznavacky\Models\AjaxResponse;

/**
 * Kontroler starající se o zpracování dat odeslaných z formuláře pro obnovení hesla
 * @author Jan Štěch
 */
class TokenPasswordChangeController extends AjaxController
{

    /**
     * Kontroler přijímající data odeslaná formulářem a volající modely, které je zpracují.
     * Také nastavuje úspěchovou nebo chybové hlášky a přesměrovává zpět nebo nastavuje pohled pro zobrazení zprávy.
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        $token = $_POST['token'];
        $pass = $_POST['pass'];
        $repass = $_POST['repass'];

        try
        {
            $passwordChanger = new TokenPasswordChanger($token, $pass, $repass);
            $passwordChanger->verifyToken();
            $passwordChanger->checkPasswords();
            $passwordChanger->changePassword();
            $passwordChanger->devalueToken();
        }
        catch (AccessDeniedException $e)
        {
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage());
            echo $response->getResponseString();
            return;
        }

        $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Heslo bylo úspěšně změněno. Za okamžik budete přesměrováni na domovskou stránku.');
        echo $response->getResponseString();
    }
}

