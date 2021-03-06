<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\DatabaseItems\Natural;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Processors\NaturalEditor;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;

/**
 * Kontroler zpracovávající data odeslaná ze stránky manage
 * @author Jan Štěch
 */
class UpdateNaturalsController extends AjaxController
{
    /**
     * Metoda odlišující, jakou akci si přeje správce třídy provést a volající příslušný model
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see AjaxController::process()
     */
    public function process(array $parameters): void
    {
        if (!isset($_POST['action']))
        {
            header('HTTP/1.0 400 Bad Request');
            return;
        }

        header('Content-Type: application/json');

        $class = $_SESSION['selection']['class'];

        try
        {
            $editor = new NaturalEditor($class);

            switch ($_POST['action'])
            {
                case 'rename':
                    $naturalId = $_POST['naturalId'];
                    $newName = $_POST['newName'];
                    $natural = new Natural(false, $naturalId);
                    $editor->rename($natural, $newName);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Přírodnina úspěšně přejmenována');
                    echo $response->getResponseString();
                    break;
                case 'merge':
                    $fromNaturalId = $_POST['fromNaturalId'];
                    $toNaturalId = $_POST['toNaturalId'];
                    $fromNatural = new Natural(false, $fromNaturalId);
                    $toNatural = new Natural(false, $toNaturalId);
                    $mergeResult = $editor->merge($fromNatural, $toNatural);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Přírodniny úspěšně sloučeny a obrázky převedeny', array('newUsesCount' => $mergeResult['mergedUses'], 'newPicturesCount' => $mergeResult['mergedPictures']));
                    echo $response->getResponseString();
                    break;
                case 'delete':
                    $naturalId = $_POST['naturalId'];
                    $natural = new Natural(false, $naturalId);
                    $editor->delete($natural);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Přírodnina úspěšně odstraněna');
                    echo $response->getResponseString();
                    break;
                default:
                    header('HTTP/1.0 400 Bad Request');
                    return;
            }
        }
        catch (AccessDeniedException $e)
        {
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage(), array('origin' => $_POST['action']));
            echo $response->getResponseString();
        }
    }
}

