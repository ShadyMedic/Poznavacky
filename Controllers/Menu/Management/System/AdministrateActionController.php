<?php
namespace Poznavacky\Controllers\Menu\Management\System;

use PHPMailer\PHPMailer\Exception;
use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Administration;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\Logger;

/**
 * Kontroler zpracovávající data odeslaná ze stránky administrate AJAX požadavkem
 * @author Jan Štěch
 */
class AdministrateActionController extends AjaxController
{
    /**
     * Metoda odlišující, jaká akce má být vykonána a volající příslušný model
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws DatabaseException
     * @throws Exception Pokud akcí bylo odeslání e-mailu a ten se nepodařilo odeslat
     * @see AjaxController::process()
     */
    public function process(array $parameters): void
    {
        if (!isset($_POST['action'])) {
            (new Logger())->warning('Uživatel s ID {userId} odeslal z IP adresy {ip} požadavek na provedení akce související se správou systému, avšak nespecifikoval typ akce',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            header('HTTP/1.0 400 Bad Request');
            return;
        }
        
        //Připrav obsah pole pro logování
        $postDataForLog = $_POST;
        if (array_key_exists('htmlMessage', $postDataForLog)) {
            $postDataForLog['htmlMessage'] = "[REDACTED]";
        } //Odeber dlouhý údaj
        if (array_key_exists('htmlFooter', $postDataForLog)) {
            $postDataForLog['htmlFooter'] = "[REDACTED]";
        } //Odeber dlouhý údaj
        $postDataForLog = print_r($postDataForLog, true);
        $postDataForLog = str_replace("\n", ' | ', $postDataForLog); //Odeber znaky nových řádků a nahraď je " | "
        $postDataForLog = substr($postDataForLog, 11,
            strlen($postDataForLog) - 18); //Odstraň "Array | ( | " ze začátku a " | ) | " z konce
        $postDataForLog = preg_replace('!\s+!', ' ', $postDataForLog); //Nahraď vícero mezer jednou
        
        header('Content-Type: application/json');
        $administration = new Administration();
        try {
            switch ($_POST['action']) {
                case 'update user':
                    $userId = $_POST['userId'];
                    $addedPics = $_POST['addedPics'];
                    $guessedPics = $_POST['guessedPics'];
                    $karma = $_POST['karma'];
                    $status = $_POST['status'];
                    
                    $values = array(
                        'addedPics' => $addedPics,
                        'guessedPics' => $guessedPics,
                        'karma' => $karma,
                        'status' => $status
                    );
                    $administration->editUser($userId, $values);
                    echo json_encode(array(
                        'messageType' => 'success',
                        'message' => 'Údaje uživatele úspěšně upraveny'
                    ));
                    break;
                case 'delete user':
                    $userId = $_POST['userId'];
                    $administration->deleteUser($userId);
                    echo json_encode(array('messageType' => 'success', 'message' => 'Uživatel úspěšně odstraněn'));
                    break;
                case 'update class':
                    $classId = $_POST['classId'];
                    $status = $_POST['status'];
                    $code = $_POST['code'];
                    
                    $values = array(
                        'status' => $status,
                        'code' => $code
                    );
                    $administration->editClass($classId, $values);
                    echo json_encode(array(
                        'messageType' => 'success',
                        'message' => 'Přístupové údaje třídy úspěšně upraveny'
                    ));
                    break;
                case 'change class admin':
                    $classId = $_POST['classId'];
                    $changedIdentifier = $_POST['changedIdentifier'];
                    $identifier = ($changedIdentifier === 'id') ? $_POST['adminId'] :
                        (($changedIdentifier === 'name') ? trim($_POST['adminName']) /* Ořež mezery*/ : null);
                    $newClassAdmin = $administration->changeClassAdmin($classId, $identifier, $changedIdentifier);
                    echo json_encode(array(
                        'messageType' => 'success',
                        'message' => 'Správce třídy byl úspěšně změněn',
                        'newName' => $newClassAdmin['name'],
                        'newId' => $newClassAdmin['id'],
                        'newEmail' => $newClassAdmin['email'],
                        'newKarma' => $newClassAdmin['karma'],
                        'newStatus' => $newClassAdmin['status']
                    ));
                    break;
                case 'delete class':
                    $classId = $_POST['classId'];
                    $administration->deleteClass($classId);
                    echo json_encode(array('messageType' => 'success', 'message' => 'Třída úspěšně odstraněna'));
                    break;
                case 'accept user name change':
                case 'accept class name change':
                case 'decline user name change':
                case 'decline class name change':
                    $requestId = $_POST['reqId'];
                    $classNameChange = (mb_stripos($_POST['action'], 'user') !== false) ? false : true;
                    $approved = (mb_stripos($_POST['action'], 'decline') !== false) ? false : true;
                    if (!$approved) {
                        $reason = $_POST['reason'];
                    } else {
                        $reason = "";
                    }
                    $result = $administration->resolveNameChange($requestId, $classNameChange, $approved, $reason);
                    if ($result) {
                        echo json_encode(array(
                            'messageType' => 'success',
                            'message' => 'Změna jména úspěšně schválena nebo zamítnuta'
                        ));
                    } else {
                        echo json_encode(array(
                            'messageType' => 'warning',
                            'message' => 'Někde se vyskytla chyba, nejspíše se nepodařilo odeslat žadatelovi e-mail.'
                        ));
                    }
                    break;
                case 'preview email':
                    $msg = $_POST['htmlMessage'];
                    $footer = $_POST['htmlFooter'];
                    $result = $administration->previewEmail($msg, $footer);
                    echo json_encode(array('content' => $result));
                    break;
                case 'send email':
                    $to = trim($_POST['addressee']); //Ořež mezery
                    $subject = trim($_POST['subject']); //Ořež mezery
                    $msg = $_POST['htmlMessage'];
                    $footer = $_POST['htmlFooter'];
                    $fromAddress = trim($_POST['fromAddress']); //Ořež mezery
                    $sender = trim($_POST['sender']); //Ořež mezery
                    $result = $administration->sendEmail($to, $subject, $msg, $footer, $sender, $fromAddress);
                    if ($result) {
                        echo json_encode(array('messageType' => 'success', 'message' => 'E-mail byl úspěšně odeslán'));
                    } else {
                        echo json_encode(array('messageType' => 'error', 'message' => 'E-mail nemohl být odeslán'));
                    }
                    break;
                case 'execute sql query':
                    $query = $_POST['query'];
                    $result = $administration->executeSqlQueries($query);
                    echo json_encode(array('dbResult' => $result));
                    break;
                default:
                    header('HTTP/1.0 400 Bad Request');
                    return;
            }
            (new Logger())->info('Uživatel s ID {userId} odeslal z IP adresy {ip} požadavek na provedení akce související se správou systému; odeslané údaje byly:{postData}',
                array(
                    'userId' => UserManager::getId(),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'postData' => $postDataForLog
                ));
        } catch (AccessDeniedException $e) {
            (new Logger())->notice('Uživatel s ID {userId} odeslal z IP adresy {ip} požadavek na provedení akce související se správou systému, avšak neuspěl kvůli následující chybě: {exception}; odeslané údaje byly:{postData}',
                array(
                    'userId' => UserManager::getId(),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'exception' => $e,
                    'postData' => $postDataForLog
                ));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage(),
                array('origin' => $_POST['action']));
            echo $response->getResponseString();
        }
    }
}

