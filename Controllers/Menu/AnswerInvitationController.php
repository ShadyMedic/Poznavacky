<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\DatabaseItems\Invitation;
use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use Poznavacky\Models\MessageBox;
use \DateTime;

/**
 * Kontroler zpracovávající data odeslaná z formuláře na přijetí nebo odmítnutí pozvánky do nějaké třídy na menu stránce
 * @author Jan Štěch
 */
class AnswerInvitationController extends SynchronousController
{
    /**
     * Metoda zpracovávající odpověď na pozvánku
     * @param array $parameters Parametry ke zpracování (nepoužíváno)
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        //Validace odeslaných dat
        if (!isset($_POST) || !isset($_POST['invitationId']) || !isset($_POST['invitationAnswer']) || filter_var($_POST['invitationId'], FILTER_VALIDATE_INT) === false)
        {
            //Jsou odeslána neplatná data v důsledku manipulace s HTML dokumentem
            (new Logger(true))->warning('Uživatel s ID {userId} odeslal požadavek na stránku pro zpracování odpovědi na pozvánku z IP adresy {ip}, avšak odeslaná data nebyla ve správném formátu', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Neplatná odpověď nebo neplatná pozvánka');
            $this->redirect('menu');
        }
        
        $invitationId = $_POST['invitationId'];
        $answer = $_POST['invitationAnswer'];
        
        //Validace hodnoty odpovědi
        if (!in_array($answer, array('accept', 'reject')))
        {
            (new Logger(true))->warning('Uživatel s ID {userId} se pokusil odpovědět na pozvánku s ID {invitationId} z IP adresy {ip}, avšak odpověď nebyla rozpoznána', array('userId' => UserManager::getId(), 'invitationId' => $invitationId, 'ip' => $_SERVER['REMOTE_ADDR']));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Neplatná odpověď');
            $this->redirect('menu');
        }
        
        //Kontrola, zda pozvánka existuje
        $invitationData = Db::fetchQuery('SELECT '.Invitation::COLUMN_DICTIONARY['user'].','.Invitation::COLUMN_DICTIONARY['class'].','.Invitation::COLUMN_DICTIONARY['expiration'].' FROM '.Invitation::TABLE_NAME.' WHERE '.Invitation::COLUMN_DICTIONARY['id'].' = ? AND '.Invitation::COLUMN_DICTIONARY['user'].' = ? AND '.Invitation::COLUMN_DICTIONARY['expiration'].' > NOW() LIMIT 1', array($invitationId, UserManager::getId()));
        if (empty($invitationData))
        {
            //Pozvánka buďto neexistuje nebo vyexpirovala nebo není určena pro přihlášeného uživatele
            (new Logger(true))->notice('Uživatel s ID {userId} se pokusil odpovědět na pozvánku s ID {invitationId} z IP adresy {ip}, avšak daná pozvánka nebyla v databázi nalezena nebo nebyla určena pro tohoto uživatele', array('userId' => UserManager::getId(), 'invitationId' => $invitationId, 'ip' => $_SERVER['REMOTE_ADDR']));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Tato pozvánka neexistuje, není určená pro vás nebo již vypršela její platnost');
            $this->redirect('menu');
        }
        
        $invitation = new Invitation(false, $invitationId);
        $invitation->initialize(UserManager::getUser(), new ClassObject(false, $invitationData[Invitation::COLUMN_DICTIONARY['class']]), new DateTime($invitationData[Invitation::COLUMN_DICTIONARY['expiration']]));
        $classId = $invitation->getClass()->getId();

        if ($answer === 'accept')
        {
            //Přijmout pozvánku
            $invitation->accept();
            $invitation->delete();
            (new Logger(true))->info('Uživatel s ID {userId} přijal pozvánku s ID {invitationId} do třídy s ID {classId} z IP adresy {ip}', array('userId' => UserManager::getId(), 'invitationId' => $invitationId, 'classId' => $classId, 'ip' => $_SERVER['REMOTE_ADDR']));
            $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Pozvánka byla přijata. Nyní máte do třídy '.$invitation->getClass()->getName().' přístup.');
            unset($invitation);
        }
        else
        {
            //Odmítnout pozvánku (pouze smazat)
            $invitation->delete();
            (new Logger(true))->info('Uživatel s ID {userId} odmítl pozvánku s ID {invitationId} do třídy s ID {classId} z IP adresy {ip}', array('userId' => UserManager::getId(), 'invitationId' => $invitationId, 'classId' => $classId, 'ip' => $_SERVER['REMOTE_ADDR']));
            $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Pozvánka byla odmítnuta a odebrána.');
            unset($invitation);
        }
        
        $this->redirect('menu');
    }
}

