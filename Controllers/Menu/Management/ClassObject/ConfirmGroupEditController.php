<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\DatabaseItems\Natural;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Processors\GroupEditor;
use Poznavacky\Models\Exceptions\DatabaseException;
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
     * @see AjaxController::process()
     */
    public function process(array $parameters): void
    {
        if (!isset($_POST['data']))
        {
            header('HTTP/1.0 400 Bad Request');
            return;
        }
        
        $group = $_SESSION['selection']['group'];
        $data = json_decode($_POST['data']);
        $editor = new GroupEditor($group);
        
        try
        {
            $groupName = $data->name;
            $partsArr = $data->parts;
            
            $editor->rename($groupName);
            $editor->unpackParts($partsArr);
            
            $editor->commit();
            
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
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_WARNING, '
                Došlo k chybě na straně serveru a změny nemohly být uloženy\n
				Kontaktujte prosím administrátora\n
				Abyste o provedené změny nepřišli, zkopírujte a uložte si prosím text níže\n
				Omlouváme se za nepříjemnosti\n
                \n
            ', array('json' => $_POST['data']));
        }
        
        echo $response->getResponseString();
    }
}

