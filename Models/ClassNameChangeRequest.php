<?php
/**
 * Třída reprezenzující žádost o změnu jména uživatele
 * @author Jan Štech
 */
class ClassNameChangeRequest extends NameChangeRequest
{
    public const TABLE_NAME = 'zadosti_jmena_tridy';
    
    public const COLUMN_DICTIONARY = array(
        'id' => 'zadosti_jmena_tridy_id',
        'subject' => 'tridy_id',
        'newName' => 'nove',
        'requestedAt' => 'cas'
    );
    
    protected const SUBJECT_CLASS_NAME = 'ClassObject';
    protected const SUBJECT_TABLE_NAME = 'tridy';
    protected const SUBJECT_NAME_DB_NAME = ClassObject::COLUMN_DICTIONARY['name'];
    
    /**
     * Metoda navracející aktuální jméno třídy
     * @return string Stávající jméno třídy
     * {@inheritDoc}
     * @see NameChangeRequest::getOldName()
     */
    public function getOldName()
    {
        return $this->subject->getName();
    }
    
    /**
     * Metoda navracející e-mail správce třídy, které se tato žádost týká
     * @return string E-mailová adresa správce třídy
     * {@inheritDoc}
     * @see NameChangeRequest::getRequestersEmail()
     */
    public function getRequestersEmail()
    {
        return $this->subject->getAdmin()[User::COLUMN_DICTIONARY['email']];
    }
    
    /**
     * Metoda odesílající správci třídy, které se tato žádost týká, e-mail o potvrzení změny jména (pokud uživatel zadal svůj e-mail)
     * {@inheritDoc}
     * @see NameChangeRequest::sendApprovedEmail()
     */
    public function sendApprovedEmail()
    {
        $addressee = $this->getRequestersEmail();
        if (empty($addressee)){ return; }   //E-mail není zadán
        $composer = new EmailComposer();
        $sender = new EmailSender();
        
        $composer->composeMail(EmailComposer::EMAIL_TYPE_CLASS_NAME_CHANGE_APPROVED, array('websiteAddress' => $_SERVER['SERVER_NAME'], 'oldName' => $this->getOldName(), 'newName' => $this->newName));
        $subject = 'Vaše žádost o změnu jména třídy '.$this->getOldName().' na '.$this->newName.' byla schválena';
        
        $sender->sendMail($addressee, $subject, $composer->getMail());
    }
    
    /**
     * Metoda odesílající správci třídy, které se tato žádost týká, e-mail o jejím zamítnutí (pokud uživatel zadal svůj e-mail)
     * @param string $reason Důvod k zamítnutí jména zadaný správcem
     * {@inheritDoc}
     * @see NameChangeRequest::sendDeclinedEmail()
     */
    public function sendDeclinedEmail(string $reason)
    {
        $addressee = $this->getRequestersEmail();
        if (empty($addressee)){ return; }   //E-mail není zadán
        $composer = new EmailComposer();
        $sender = new EmailSender();
        
        $composer->composeMail(EmailComposer::EMAIL_TYPE_CLASS_NAME_CHANGE_DECLINED, array('websiteAddress' => $_SERVER['SERVER_NAME'], 'oldName' => $this->getOldName(), 'declineReason' => $reason));
        $subject = 'Vaše žádost o změnu jména třídy '.$this->getOldName().' byla zamítnuta';
        
        $sender->sendMail($addressee, $subject, $composer->getMail());
    }
}