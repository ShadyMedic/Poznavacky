<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\DatabaseItems\Natural;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Processors\GroupEditor;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use \Exception;

/**
 * Kontroler zpracovávající data o změně poznávačky odeslaná ze stránky edit
 * @author Jan Štěch
 */
class ConfirmGroupEditController extends AjaxController
{
    /**
     * Metoda dekódující odeslaný JSON string a volající příslušný model
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws DatabaseException
     * @see AjaxController::process()
     */
    public function process(array $parameters): void
    {
        if (!isset($_POST['data']))
        {
            (new Logger(true))->warning('Uživatel s ID {userId} odeslal z IP adresy {ip} požadavek na úpravu poznávačky s ID {groupId} patřící do třídy s ID {classId}, avšak nebyla v něm odeslány žádné informace o úpravách', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'groupId' => $_SESSION['selection']['group']->getId(), 'classId' => $_SESSION['selection']['class']->getId()));
            header('HTTP/1.0 400 Bad Request');
            return;
        }
        
        $group = $_SESSION['selection']['group'];
        $dataString = $_POST['data'];
        $data = json_decode($dataString);
        $editor = new GroupEditor($group);
        
        try
        {
            $groupName = trim($data->name); //Ořež mezery
            $partsArr = $data->parts;
            
            $editor->rename($groupName);
            $editor->unpackParts($partsArr);
            
            $editor->commit();

            (new Logger(true))->info('Uživatel s ID {userId} odeslal z IP adresy {ip} požadavek na úpravu poznávačky s ID {groupId} patřící do třídy s ID {classId} a úpravy popsal následujícím JSON řetězcem: {json}', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'groupId' => $group->getId(), 'classId' => $_SESSION['selection']['class']->getId(), 'json' => $dataString));

            //Navrať nový seznam dostupných přírodnin
            $jsonNaturals = json_encode(array_map(function (Natural $natural): string {return $natural->getName(); }, $_SESSION['selection']['class']->getNaturals()));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, $jsonNaturals, array());
        }
        catch (AccessDeniedException $e)
        {
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage(), array());
        }
        catch (DatabaseException|Exception $e)
        {
            (new Logger(true))->error('Uživatel s ID {userId} odeslal z IP adresy {ip} požadavek na úpravu poznávačky s ID {groupId} patřící do třídy s ID {classId}, ale uložení změn zabránila neočekávaná chyba: {exception}; úpravy byly popsány následujícím JSON řetězcem: {json}', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'groupId' => $group->getId(), 'classId' => $_SESSION['selection']['class']->getId(), 'exception' => $e, 'json' => $dataString));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_WARNING, '
                Došlo k chybě na straně serveru a změny nemohly být uloženy.
				Kontaktujte prosím administrátora.
				Abyste o provedené změny nepřišli, zkopírujte a uložte si prosím text níže.
				Omlouváme se za nepříjemnosti.
            ', array('json' => $dataString));
        }
        
        echo $response->getResponseString();
    }
}

