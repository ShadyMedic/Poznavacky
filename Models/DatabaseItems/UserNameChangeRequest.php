<?php
namespace Poznavacky\Models\DatabaseItems;

use PHPMailer\PHPMailer\Exception;
use Poznavacky\Models\Emails\EmailComposer;
use Poznavacky\Models\Emails\EmailSender;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\Db;

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
    
    
    protected const NON_PRIMITIVE_PROPERTIES = array(
        'subject' => User::class
    );
    
    protected const CAN_BE_CREATED = true;
    protected const CAN_BE_UPDATED = true;
    
    /**
     * Metoda navracející aktuální jméno uživatele
     * @return string Stávající jméno uživatele
     * {@inheritDoc}
     * @throws DatabaseException
     * @see NameChangeRequest::getOldName()
     */
    public function getOldName(): string
    {
        $this->loadIfNotLoaded($this->subject);
        return $this->subject['name'];
    }
    
    /**
     * Metoda navracející e-mail uživatele žádající o změnu svého jména
     * @return string E-mailová adresa autora této žádosti
     * {@inheritDoc}
     * @throws DatabaseException
     * @see NameChangeRequest::getRequestersEmail()
     */
    public function getRequestersEmail(): string
    {
        $this->loadIfNotLoaded($this->subject);
        return $this->subject[User::COLUMN_DICTIONARY['email']];
    }
    
    /**
     * Metoda schvalující tuto žádost
     * Jméno uživatele je změněno a žadatel obdrží e-mail (pokud jej zadal)
     * @return TRUE, pokud se vše povedlo, FALSE, pokud se nepodařilo odeslat e-mail
     * @throws DatabaseException
     */
    public function approve(): bool
    {
        $this->loadIfNotLoaded($this->newName);
        $this->loadIfNotLoaded($this->subject);
        
        $this->subject['name'];
        
        Db::executeQuery('UPDATE '.User::TABLE_NAME.' SET '.User::COLUMN_DICTIONARY['name'].' = ? WHERE '.
                         User::COLUMN_DICTIONARY['id'].'= ?;', array($this->newName, $this->subject->getId()));
        
        //Odeslat e-mail
        return $this->sendApprovedEmail();
    }
    
    /**
     * Metoda odesílající autorovi této žádosti e-mail o potvrzení změny jména (pokud uživatel zadal svůj e-mail)
     * @return bool TRUE, pokud se e-mail podařilo odeslat, FALSE, pokud ne
     * {@inheritDoc}
     * @throws Exception Pokud se nepodaří e-mail odeslat
     * @throws DatabaseException
     * @see NameChangeRequest::sendApprovedEmail()
     */
    public function sendApprovedEmail(): bool
    {
        $addressee = $this->getRequestersEmail();
        if (!$this->isDefined($addressee)) {
            return false;
        }   //E-mail není zadán
        $composer = new EmailComposer();
        $sender = new EmailSender();
        
        $composer->composeMail(EmailComposer::EMAIL_TYPE_USER_NAME_CHANGE_APPROVED, array(
            'websiteAddress' => $_SERVER['SERVER_NAME'],
            'oldName' => $this->getOldName(),
            'newName' => $this->newName
        ));
        $subject = 'Vaše žádost o změnu jména na '.$this->newName.' byla přijata';
        
        return $sender->sendMail($addressee, $subject, $composer->getMail());
    }
    
    /**
     * Metoda odesílající autorovi této žádosti e-mail o jejím zamítnutí (pokud uživatel zadal svůj e-mail)
     * @param string $reason Důvod k zamítnutí jména zadaný správcem
     * @return bool TRUE, pokud se e-mail podařilo odeslat, FALSE, pokud ne
     * {@inheritDoc}
     * @throws Exception Pokud se nepodaří e-mail odeslat
     * @throws DatabaseException
     * @see NameChangeRequest::sendDeclinedEmail()
     */
    public function sendDeclinedEmail(string $reason): bool
    {
        $addressee = $this->getRequestersEmail();
        if (!$this->isDefined($addressee)) {
            return false;
        }   //E-mail není zadán
        $composer = new EmailComposer();
        $sender = new EmailSender();
        
        $composer->composeMail(EmailComposer::EMAIL_TYPE_USER_NAME_CHANGE_DECLINED, array(
            'websiteAddress' => $_SERVER['SERVER_NAME'],
            'oldName' => $this->getOldName(),
            'declineReason' => $reason
        ));
        $subject = 'Vaše žádost o změnu jména byla zamítnuta';
        
        return $sender->sendMail($addressee, $subject, $composer->getMail());
    }
}

