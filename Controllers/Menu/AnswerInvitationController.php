<?php
namespace Poznavacky\Controllers\Menu;

/**
 * Kontroler zpracovávající data odeslaná z formuláře na přijetí nebo odmítnutí pozvánky do nějaké třídy na menu stránce
 * @author Jan Štěch
 */
class AnswerInvitationController extends Controller
{
    /**
     * Metoda zpracovávající odpověď na pozvánku
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        //Validace odeslaných dat
        if (!isset($_POST) || !isset($_POST['invitationId']) || !isset($_POST['invitationAnswer']) || filter_var($_POST['invitationId'], FILTER_VALIDATE_INT) === false)
        {
            //Jsou odeslána neplatná data v důsledku manipulace s HTML dokumentem
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Neplatná odpověď nebo neplatná pozvánka');
            $this->redirect('menu');
        }
        
        $invitationId = $_POST['invitationId'];
        $answer = $_POST['invitationAnswer'];
        
        //Validace hodnoty odpovědi
        if (!in_array($answer, array('accept', 'reject')))
        {
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Neplatná odpověď');
            $this->redirect('menu');
        }
        
        //Kontrola, zda pozvánka existuje
        $invitationData = Db::fetchQuery('SELECT '.Invitation::COLUMN_DICTIONARY['user'].','.Invitation::COLUMN_DICTIONARY['class'].','.Invitation::COLUMN_DICTIONARY['expiration'].' FROM '.Invitation::TABLE_NAME.' WHERE '.Invitation::COLUMN_DICTIONARY['id'].' = ? AND '.Invitation::COLUMN_DICTIONARY['user'].' = ? AND '.Invitation::COLUMN_DICTIONARY['expiration'].' > NOW() LIMIT 1', array($invitationId, UserManager::getId()));
        if (empty($invitationData))
        {
            //Pozvánka buďto neexistuje nebo vyexpirovala nebo není určena pro přihlášeného uživatele
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Tato pozvánka neexistuje, není určená pro vás nebo již vypršela její platnost');
            $this->redirect('menu');
        }
        
        $invitation = new Invitation(false, $invitationId);
        $invitation->initialize(UserManager::getUser(), new ClassObject(false, $invitationData[Invitation::COLUMN_DICTIONARY['class']]), new DateTime($invitationData[Invitation::COLUMN_DICTIONARY['expiration']]));
        
        if ($answer === 'accept')
        {
            //Přijmout pozvánku
            $invitation->accept();
            $invitation->delete();
            $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Pozvánka byla přijata. Nyní máte do třídy '.$invitation->getClass()->getName().' přístup.');
            unset($invitation);
        }
        else
        {
            //Odmítnout pozvánku (pouze smazat)
            $invitation->delete();
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Pozvánka byla odmítnuta a odebrána.');
            unset($invitation);
        }
        
        $this->redirect('menu');
    }
}

