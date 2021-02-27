<?php
namespace Poznavacky\Controllers\Menu\Study\AddPictures;

use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Processors\PictureAdder;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;

/**
 * Kontroler starající se o příjem dat z formuláře pro přidání obrázku a o jejich zpracování
 * @author Jan Štěch
 */
class SubmitPictureController extends \Poznavacky\Controllers\Controller
{

    /**
     * Metoda ověřující, zda má uživatel do třídy přístup a volající model pro uložení nového obrázku
     * @see Controller::process()
     */
    function process(array $parameters): void
    {
        $class = $_SESSION['selection']['class'];
        $group = $_SESSION['selection']['group'];

        //Kontrola přístupu je provedena již v MenuController.php

        //Kontrola, zda byl tento kontroler zavolán jako AJAX
        if (empty($_POST))
        {
            
            header('HTTP/1.0 400 Bad Request');
            exit();
        }

        $adder = new PictureAdder($group);
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