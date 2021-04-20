<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\DatabaseItems\Invitation;
use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\Logger;
use \DateTime;
use \Exception;

/**
 * Kontroler zpracovávající data odeslaná z formuláře na přijetí nebo odmítnutí pozvánky do nějaké třídy na menu stránce
 * @author Jan Štěch
 */
class AnswerInvitationController extends AjaxController
{
    /**
     * Metoda zpracovávající odpověď na pozvánku
     * @param array $parameters Parametry ke zpracování, prvním prvkem pole musí být URL třídy, na pozvánku do níž
     *     odpovídáme, druhým řetězec "accept" nebo "reject", podle odpovědi na pozvánku
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     * @throws Exception Pokud se nepodaří vytvořit objekt DateTime
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        //Validace odeslaných dat
        if (!isset($parameters) || count($parameters) !== 2 || !in_array($parameters[1], array('accept', 'reject'))) {
            //Jsou odeslána neplatná data v důsledku manipulace s HTML dokumentem
            (new Logger(true))->warning('Uživatel s ID {userId} odeslal požadavek na stránku pro zpracování odpovědi na pozvánku z IP adresy {ip}, avšak odeslaná data nebyla ve správném formátu',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, 'Neplatná odpověď nebo neplatná pozvánka');
            echo $response->getResponseString();
            return;
        }
        
        $classUrl = $parameters[0];
        $accepted = ($parameters[1] === "accept");
        
        //Kontrola, zda pozvánka existuje
        $invitationData = Db::fetchQuery('SELECT '.Invitation::COLUMN_DICTIONARY['id'].','.
                                         Invitation::COLUMN_DICTIONARY['class'].','.
                                         Invitation::COLUMN_DICTIONARY['expiration'].' FROM '.Invitation::TABLE_NAME.
                                         ' WHERE '.Invitation::COLUMN_DICTIONARY['class'].' = (SELECT '.
                                         ClassObject::COLUMN_DICTIONARY['id'].' FROM '.ClassObject::TABLE_NAME.
                                         ' WHERE '.ClassObject::COLUMN_DICTIONARY['url'].' = ?) AND '.
                                         Invitation::COLUMN_DICTIONARY['user'].' = ? AND '.
                                         Invitation::COLUMN_DICTIONARY['expiration'].' > NOW();',
            array($classUrl, UserManager::getId()));
        if (empty($invitationData)) {
            //Pozvánka buďto neexistuje nebo vyexpirovala nebo není určena pro přihlášeného uživatele
            (new Logger(true))->notice('Uživatel s ID {userId} se pokusil odpovědět na pozvánku do třídy s URL {classUrl} z IP adresy {ip}, avšak daná pozvánka nebyla v databázi nalezena nebo nebyla určena pro tohoto uživatele',
                array('userId' => UserManager::getId(), 'classUrl' => $classUrl, 'ip' => $_SERVER['REMOTE_ADDR']));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR,
                'Tato pozvánka neexistuje, není určená pro vás nebo již vypršela její platnost');
            echo $response->getResponseString();
            return;
        }
        
        $invitationId = $invitationData[Invitation::COLUMN_DICTIONARY['id']];
        $classId = $invitationData[Invitation::COLUMN_DICTIONARY['class']];
        $expiration = $invitationData[Invitation::COLUMN_DICTIONARY['expiration']];
        $invitation = new Invitation(false, $invitationId);
        $invitation->initialize(UserManager::getUser(), new ClassObject(false, $classId), new DateTime($expiration));
        
        if ($accepted) {
            //Přijmout pozvánku
            $invitation->accept();
            $invitation->delete();
            (new Logger(true))->info('Uživatel s ID {userId} přijal pozvánku s ID {invitationId} do třídy s ID {classId} z IP adresy {ip}',
                array(
                    'userId' => UserManager::getId(),
                    'invitationId' => $invitationId,
                    'classId' => $classId,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS,
                'Pozvánka byla přijata. Nyní máte do třídy '.$invitation->getClass()->getName().' přístup.');
        } else {
            //Odmítnout pozvánku (pouze smazat)
            $invitation->delete();
            (new Logger(true))->info('Uživatel s ID {userId} odmítl pozvánku s ID {invitationId} do třídy s ID {classId} z IP adresy {ip}',
                array(
                    'userId' => UserManager::getId(),
                    'invitationId' => $invitationId,
                    'classId' => $classId,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Pozvánka byla odmítnuta a odebrána.');
        }
        
        echo $response->getResponseString();
    }
}

