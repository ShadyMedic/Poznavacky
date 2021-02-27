<?php
namespace Poznavacky\Controllers\Menu\Study\AddPictures;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Processors\PictureAdder;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\Logger;

/**
 * AJAX kontroler starající se o příjem dat z formuláře pro přidání obrázku a o jejich zpracování
 * @author Jan Štěch
 */
class SubmitPictureController extends Controller
{

    /**
     * Metoda ověřující, zda má uživatel do třídy přístup a volající model pro uložení nového obrázku
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see Controller::process()
     */
    function process(array $parameters): void
    {
        $group = $_SESSION['selection']['group'];

        //Kontrola přístupu je provedena již v MenuController.php

        //Kontrola, zda byl tento kontroler zavolán jako AJAX
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest' )
        {
            
            (new Logger(true))->warning('Uživatel s ID {userId} se pokusil přistoupit ke kontroleru submit-picture z IP adresy {ip} aniž by odeslal jakákoli POST data (zřejmě odeslal ne-AJAX požadavek)', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            header('HTTP/1.0 400 Bad Request');
            exit();
        }

        $adder = new PictureAdder($group);
        $response = null;
        try
        {
            if ($adder->processFormData($_POST))
            {
                $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, "Obrázek úspěšně přidán");
            }
        }
        catch (AccessDeniedException $e)
        {
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage());
        }

        echo $response->getResponseString();

        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}