<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\DatabaseItems\Natural;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Processors\NaturalEditor;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\Logger;

/**
 * Kontroler zpracovávající data odeslaná ze stránky manage
 * @author Jan Štěch
 */
class UpdateNaturalsController extends AjaxController
{
    /**
     * Metoda odlišující, jakou akci si přeje správce třídy provést a volající příslušný model
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws DatabaseException
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
                    $newName = trim($_POST['newName']); //Ořež mezery
                    $natural = new Natural(false, $naturalId);
                    $editor->rename($natural, $newName);
                    (new Logger(true))->info('Uživatel s ID {userId} přejmenoval ve třídě s ID {classId} přírodninu s ID {naturalId} na {newName} z IP adresy {ip}', array('userId' => UserManager::getId(), 'classId' => $_SESSION['selection']['class']->getId(), 'naturalId' => $naturalId, 'newName' => $newName, 'ip' => $_SERVER['REMOTE_ADDR']));
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Přírodnina úspěšně přejmenována');
                    echo $response->getResponseString();
                    break;
                case 'merge':
                    $fromNaturalId = $_POST['fromNaturalId'];
                    $toNaturalId = $_POST['toNaturalId'];
                    $fromNatural = new Natural(false, $fromNaturalId);
                    $toNatural = new Natural(false, $toNaturalId);
                    $mergeResult = $editor->merge($fromNatural, $toNatural);
                    (new Logger(true))->info('Uživatel s ID {userId} sloučil ve třídě s ID {classId} přírodninu s ID {fromNaturalId} do přírodniny s ID {toNaturalId} z IP adresy {ip}', array('userId' => UserManager::getId(), 'classId' => $_SESSION['selection']['class']->getId(), 'fromNaturalId' => $fromNaturalId, 'toNaturalId' => $toNaturalId, 'ip' => $_SERVER['REMOTE_ADDR']));
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Přírodniny úspěšně sloučeny a obrázky převedeny', array('newUsesCount' => $mergeResult['mergedUses'], 'newPicturesCount' => $mergeResult['mergedPictures']));
                    echo $response->getResponseString();
                    break;
                case 'delete':
                    $naturalId = $_POST['naturalId'];
                    $natural = new Natural(false, $naturalId);
                    $editor->delete($natural);
                    (new Logger(true))->info('Uživatel s ID {userId} odstranil ze třídy s ID {classId} přírodninu s ID {naturalId} z IP adresy {ip}', array('userId' => UserManager::getId(), 'classId' => $_SESSION['selection']['class']->getId(), 'naturalId' => $naturalId, 'ip' => $_SERVER['REMOTE_ADDR']));
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

