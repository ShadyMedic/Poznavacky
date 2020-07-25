<?php
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
    public function process(array $parameters)
    {
        //Validace odeslaných dat
        DebugLogger::debugLog(gettype($_POST['invitationId']));
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
        Db::connect();
        $invitationData = Db::fetchQuery('SELECT uzivatele_id,tridy_id,expirace FROM pozvanky WHERE pozvanky_id = ? AND uzivatele_id = ? AND expirace > NOW() LIMIT 1', array($invitationId, UserManager::getId()));
        if (empty($invitationData))
        {
            //Pozvánka buďto neexistuje nebo vyexpirovala nebo není určena pro přihlášeného uživatele
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Tato pozvánka neexistuje, není určená pro vás nebo již vypršela její platnost');
            $this->redirect('menu');
        }
        
        $invitation = new Invitation($invitationId);
        $invitation->initialize(UserManager::getUser(), new ClassObject($invitationData['tridy_id']), new DateTime($invitationData['expirace']));
        
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