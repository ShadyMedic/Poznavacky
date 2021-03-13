<?php
namespace Poznavacky\Controllers\Menu;

use BadMethodCallException;
use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\Logger;

/**
 * Kontroler starající se o opuštění třídy
 * @author Jan Štěch
 */
class LeaveController extends AjaxController
{
    /**
     * Metoda odstraňujícího přihlášeného uživatele ze zvolené třídy (ruší jeho členství)
     * @param array $parameters Parametry pro zpracování kontrolerem, prvním prvkem musí být URL opouštěné třídy
     * @throws AccessDeniedException Pokud není přihlhášen žádný uživatel
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        $userId = UserManager::getId();
        $class = new ClassObject(false);
        $class->initialize(null, $parameters[0]);
        try
        {
            $class->load();
            $classId = $class->getId();
        }
        catch (BadMethodCallException $e)
        {
            (new Logger(true))->warning('Uživatel s ID {userId} se pokusil opustit třídu s URL {classUrl} z IP adresy {ip}, avšak taková třída nebyla v databázi nalezena', array('userId' => UserManager::getId(), 'classUrl' => $parameters[0], 'ip' => $_SERVER['REMOTE_ADDR']));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, 'Třída se zadaným URL nebyla nalezena');
            echo $response->getResponseString();
            return;
        }
        catch (DatabaseException $e)
        {
            (new Logger(true))->warning('Uživatel s ID {userId} se pokusil opustit třídu s URL {classUrl} z IP adresy {ip}, avšak zabránila mu v tom nečekaná chyba databáze (hláška {exception})', array('userId' => UserManager::getId(), 'classUrl' => $parameters[0], 'ip' => $_SERVER['REMOTE_ADDR'], 'exception' => $e));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, AccessDeniedException::REASON_UNEXPECTED);
            echo $response->getResponseString();
            return;
        }

        try
        {
            if ($class->checkAdmin($userId))
            {
                //Správce třídy jí nemůže opustit
                (new Logger(true))->notice('Uživatel s ID {userId} se pokusil opustit třídu s ID {classId} z IP adresy {ip}, avšak jelikož je její správce, nebylo mu toto umožněno', array('userId' => $userId, 'classId' => $classId, 'ip' => $_SERVER['REMOTE_ADDR']));
                $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, 'Jako správce třídy nemůžete třídu opustit');
                echo $response->getResponseString();
                return;
            }

            $class->removeMember($userId);
        }
        catch (AccessDeniedException $e)
        {
            (new Logger(true))->warning('Uživatel s ID {userId} se pokusil opustit třídu s ID {classId} z IP adresy {ip}, avšak zabránila mu v tom chyba: {exception}', array('userId' => UserManager::getId(), 'classId' => $classId, 'ip' => $_SERVER['REMOTE_ADDR'], 'exception' => $e));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage());
            echo $response->getResponseString();
            return;
        }
        catch (DatabaseException $e)
        {
            (new Logger(true))->error('Uživatel s ID {userId} se pokusil opustit třídu s ID {classId} z IP adresy {ip}, avšak zabránila mu v tom nečekaná chyba databáze (hláška {exception})', array('userId' => UserManager::getId(), 'classId' => $classId, 'ip' => $_SERVER['REMOTE_ADDR'], 'exception' => $e));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, AccessDeniedException::REASON_UNEXPECTED);
            echo $response->getResponseString();
            return;
        }

        (new Logger(true))->info('Uživatel s ID {userId} opustil třídu s ID {classId} z IP adresy {ip}', array('userId' => $userId, 'classId' => $classId, 'ip' => $_SERVER['REMOTE_ADDR']));
        $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Třída úspěšně opuštěna');
        echo $response->getResponseString();
    }
}

