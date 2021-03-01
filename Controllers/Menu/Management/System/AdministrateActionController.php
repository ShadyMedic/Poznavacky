<?php
namespace Poznavacky\Controllers\Menu\Management\System;

use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Administration;
use Poznavacky\Models\AjaxResponse;

/**
 * Kontroler zpracovávající data odeslaná ze stránky administrate AJAX požadavkem
 * @author Jan Štěch
 */
class AdministrateActionController extends AjaxController
{
    /**
     * Metoda odlišující, jaká akce má být vykonána a volající příslušný model
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see AjaxController::process()
     */
    public function process(array $parameters): void
    {
        if (!isset($_POST['action']))
        {
            header('HTTP/1.0 400 Bad Request');
            exit();
        }
        
        //Kontrola, zda je nějaký uživatel přihlášen
        $aChecker = new AccessChecker();
        if (!$aChecker->checkUser())
        {
            header('HTTP/1.0 403 Forbidden');
            exit();
        }
        //Kontrola, zda je přihlášený uživatel administrátorem
        if (!$aChecker->checkSystemAdmin())
        {
            header('HTTP/1.0 403 Forbidden');
            exit();
        }
        
        header('Content-Type: application/json');
        $administration = new Administration();
        try
        {
            switch ($_POST['action'])
            {
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
                    echo json_encode(array('messageType' => 'success', 'message' => 'Údaje uživatele úspěšně upraveny'));
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
                    echo json_encode(array('messageType' => 'success', 'message' => 'Přístupové údaje třídy úspěšně upraveny'));
                    break;
                case 'change class admin':
                    $classId = $_POST['classId'];
                    $changedIdentifier = $_POST['changedIdentifier'];
                    $identifier = ($changedIdentifier === 'id') ? $_POST['adminId'] : (($changedIdentifier === 'name') ? $_POST['adminName'] : null);
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
                    if (!$approved){ $reason = $_POST['reason']; }
                    else { $reason = ""; }
                    $result = $administration->resolveNameChange($requestId, $classNameChange, $approved, $reason);
                    if ($result) { echo json_encode(array('messageType' => 'success', 'message' => 'Změna jména úspěšně schválena nebo zamítnuta'));}
                    else { echo json_encode(array('messageType' => 'warning', 'message' => 'Někde se vyskytla chyba, nejspíše se nepodařilo odeslat žadatelovi e-mail.')); }
                    break;
                case 'preview email':
                    $msg = $_POST['htmlMessage'];
                    $footer = $_POST['htmlFooter'];
                    $result = $administration->previewEmail($msg, $footer);
                    echo json_encode(array('content' => $result));
                    break;
                case 'send email':
                    $to = $_POST['addressee'];
                    $subject = $_POST['subject'];
                    $msg = $_POST['htmlMessage'];
                    $footer = $_POST['htmlFooter'];
                    $fromAddress = $_POST['fromAddress'];
                    $sender = $_POST['sender'];
                    $result = $administration->sendEmail($to, $subject, $msg, $footer, $sender, $fromAddress);
                    if ($result) { echo json_encode(array('messageType' => 'success', 'message' => 'E-mail byl úspěšně odeslán')); }
                    else { echo json_encode(array('messageType' => 'error', 'message' => 'E-mail nemohl být odeslán')); }
                    break;
                case 'execute sql query':
                    $query = $_POST['query'];
                    $result = $administration->executeSqlQueries($query);
                    echo json_encode(array('dbResult' => $result));
                    break;
                default:
                    header('HTTP/1.0 400 Bad Request');
                    exit();
            }
        }
        catch (AccessDeniedException $e)
        {
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage(), array('origin' => $_POST['action']));
            echo $response->getResponseString();
        }
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}

