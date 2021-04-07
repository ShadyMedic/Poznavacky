<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\DatabaseItems\Group;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Exceptions\NoDataException;
use Poznavacky\Models\Processors\GroupAdder;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\Logger;
use \BadMethodCallException;

/**
 * Kontroler zpracovávající data odeslaná ze stránky manage
 * @author Jan Štěch
 */
class ClassUpdateController extends AjaxController
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
        //Kontrola, zda je zvolena nějaká třída
        $class = $_SESSION['selection']['class'];
        
        try
        {
            switch ($_POST['action'])
            {
                case 'request name change':
                    $newName = $_POST['newName'];
                    $class->requestNameChange($newName);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Žádost o změnu názvu třídy byla odeslána. Sledujte prosím svou e-mailovou schránku (pokud jste si zde nastavili e-mailovou adresu). V okamžiku, kdy vaši žádost posoudí správce, dostanete zprávu.');
                    echo $response->getResponseString();
                    break;
                case 'update access':
                    $newStatus = @ClassObject::CLASS_STATUSES_DICTIONARY[mb_strtolower($_POST['newStatus'])];
                    if (empty($newStatus)){ $newStatus = 'unknown'; }
                    $newCode = $_POST['newCode'];
                    $class->updateAccessData($newStatus, $newCode);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Přístupová data třídy byla úspěšně změněna');
                    echo $response->getResponseString();
                    break;
                case 'kick member':
                    $kickedUserId = $_POST['memberId'];
                    $class->removeMember($kickedUserId);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Uživatel byl úspěšně odebrán ze třídy');
                    echo $response->getResponseString();
                    break;
                case 'invite user':
                    $invitedUserName = $_POST['userName'];
                    $class->inviteUser($invitedUserName);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Pozvánka úspěšně odeslána');
                    echo $response->getResponseString();
                    break;
                case 'create test':
                    $adder = new GroupAdder($class);
                    $group = $adder->processFormData($_POST);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Poznávačka '.$_POST['testName'].' úspěšně vytvořena', array('newGroupData' => array(
                        'id' => $group->getId(),
                        'name' => $group->getName(),
                        'url' => $group->getUrl(),
                        'parts' => $group->getPartsCount()
                        )));
                    echo $response->getResponseString();
                    break;
                case 'delete test':
                    $deletedTestId = $_POST['testId'];
                    $test = new Group(false, $deletedTestId);
                    try { $class->removeGroup($test); } catch (BadMethodCallException $e) { throw new NoDataException(NoDataException::UNKNOWN_GROUP); }
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Poznávačka byla odstraněna');
                    echo $response->getResponseString();
                    break;
                case 'delete class':
                    $adminPassword = $_POST['password'];
                    $class->deleteAsClassAdmin($adminPassword);
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Třída byla odstraněna');
                    echo $response->getResponseString();
                    break;
                case 'verify password':
                    $password = urldecode($_POST['password']);
                    if (mb_strlen($password) === 0)
                    {
                        (new Logger(true))->notice('Ověření hesla uživatele s ID {userId} na stránce pro správu třídy s ID {classId}, přistupujícího do systému z IP adresy {ip} selhalo, protože žádné heslo nebylo vyplněno', array('userId' => UserManager::getId(), 'classId' => $_SESSION['selection']['class']->getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
                        throw new AccessDeniedException(AccessDeniedException::REASON_NO_PASSWORD_GENERAL);
                    }
                    $aChecker = new AccessChecker();
                    if (!$aChecker->recheckPassword($password))
                    {
                        (new Logger(true))->notice('Ověření hesla uživatele s ID {userId} na stránce pro správu třídy s ID {classId}, přistupujícího do systému z IP adresy {ip} selhalo, protože zadané heslo nebylo správné', array('userId' => UserManager::getId(), 'classId' => $_SESSION['selection']['class']->getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
                        throw new AccessDeniedException(AccessDeniedException::REASON_WRONG_PASSWORD_GENERAL);
                    }
                    $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, '', array('verified' => true));
                    (new Logger(true))->info('Ověření hesla uživatele s ID {userId} na stránce pro správu třídy s ID {classId}, přistupujícího do systému z IP adresy {ip} bylo úspěšné', array('userId' => UserManager::getId(), 'classId' => $_SESSION['selection']['class']->getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
                    echo $response->getResponseString();
                    break;
                default:
                    header('HTTP/1.0 400 Bad Request');
                    return;
            }
        }
        catch (AccessDeniedException | NoDataException $e)
        {
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage(), array('origin' => $_POST['action']));
            echo $response->getResponseString();
        }
    }
}

