<?php
namespace Poznavacky\Controllers\Menu\Study\AddPictures;

use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Processors\PictureAdder;
use Poznavacky\Models\AjaxResponse;

/**
 * AJAX kontroler starající se o příjem dat z formuláře pro přidání obrázku a o jejich zpracování
 * @author Jan Štěch
 */
class SubmitPictureController extends AjaxController
{

    /**
     * Metoda ověřující, zda má uživatel do třídy přístup a volající model pro uložení nového obrázku
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws DatabaseException
     * @see AjaxController::process()
     */
    function process(array $parameters): void
    {
        $group = $_SESSION['selection']['group'];

        //Kontrola přístupu je provedena již v MenuController.php

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
    }
}

