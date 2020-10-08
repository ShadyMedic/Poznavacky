<?php
/**
 * Třída reprezenzující žádost o změnu jména uživatele
 * @author Jan Štech
 */
class UserNameChangeRequest extends NameChangeRequest
{
    public const TABLE_NAME = 'zadosti_jmena_uzivatele';
    
    public const COLUMN_DICTIONARY = array(
        'id' => 'zadosti_jmena_uzivatele_id',
        'subject' => 'uzivatele_id',
        'newName' => 'nove',
        'requestedAt' => 'cas'
    );
    
    protected const SUBJECT_CLASS_NAME = 'User';
    protected const SUBJECT_TABLE_NAME = 'uzivatele';
    protected const SUBJECT_NAME_DB_NAME = User::COLUMN_DICTIONARY['name'];
    
    /**
     * Metoda navracející aktuální jméno uživatele
     * @return string Stávající jméno uživatele
     * {@inheritDoc}
     * @see NameChangeRequest::getOldName()
     */
    public function getOldName()
    {
        $this->loadIfNotLoaded($this->subject);
        return $this->subject['name'];
    }
    
    /**
     * Metoda navracející e-mail uživatele žádající o změnu svého jména
     * @return string E-mailová adresa autora této žádosti
     * {@inheritDoc}
     * @see NameChangeRequest::getRequestersEmail()
     */
    public function getRequestersEmail()
    {
        $this->loadIfNotLoaded($this->subject);
        return $this->subject[User::COLUMN_DICTIONARY['email']];
    }
    
    /**
     * Metoda odesílající autorovi této žádosti e-mail o potvrzení změny jména (pokud uživatel zadal svůj e-mail)
     * {@inheritDoc}
     * @see NameChangeRequest::sendApprovedEmail()
     */
    public function sendApprovedEmail()
    {
        $addressee = $this->getRequestersEmail();
        if (!$this->isDefined($addressee)){ return; }   //E-mail není zadán
        $composer = new EmailComposer();
        $sender = new EmailSender();
        
        $composer->composeMail(EmailComposer::EMAIL_TYPE_USER_NAME_CHANGE_APPROVED, array('websiteAddress' => $_SERVER['SERVER_NAME'], 'oldName' => $this->getOldName(), 'newName' => $this->newName));
        $subject = 'Vaše žádost o změnu jména na '.$this->newName.' byla přijata';
        
        $sender->sendMail($addressee, $subject, $composer->getMail());
    }
    
    /**
     * Metoda odesílající autorovi této žádosti e-mail o jejím zamítnutí (pokud uživatel zadal svůj e-mail)
     * @param string $reason Důvod k zamítnutí jména zadaný správcem
     * {@inheritDoc}
     * @see NameChangeRequest::sendDeclinedEmail()
     */
    public function sendDeclinedEmail(string $reason)
    {
        $addressee = $this->getRequestersEmail();
        if (!$this->isDefined($addressee)){ return; }   //E-mail není zadán
        $composer = new EmailComposer();
        $sender = new EmailSender();
        
        $composer->composeMail(EmailComposer::EMAIL_TYPE_USER_NAME_CHANGE_DECLINED, array('websiteAddress' => $_SERVER['SERVER_NAME'], 'oldName' => $this->getOldName(), 'declineReason' => $reason));
        $subject = 'Vaše žádost o změnu jména byla zamítnuta';
        
        $sender->sendMail($addressee, $subject, $composer->getMail());
    }
}