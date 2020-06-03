<?php
/** 
 * Třída reprezentujcí žádost o změnu jména uživatele nebo třídy
 * @author Jan Štěch
 */
class NameChangeRequest
{
    const TYPE_CLASS = 'class';
    const TYPE_USER = 'user';
    
    private $id;
    private $type;
    private $object;
    private $newName;
    private $requestedAt;
    
    /**
     * Konstruktor žádosti o změnu jména
     * @param int $id ID žádosti v databázi
     * @param object $object Instance třídy ClassObject, pokud žádost požaduje změnu jména třídy, nebo instance třídy User, pokud žádost požaduje změnu jména uživatele
     * @param string $newName Požadované nové jméno třídy nebo uživatele
     * @param DateTime $requestedTime Čas, ve kterém byla žádost podána
     * @throws BadMethodCallException V případě, že první argument není instance ClassObject nebo User
     */
    public function __construct(int $id, object $object, string $newName, DateTime $requestedTime)
    {
        if ($object instanceof ClassObject)
        {
            $this->object = $object;
            $this->type = self::TYPE_CLASS;
        }
        else if ($object instanceof User)
        {
            $this->object = $object;
            $this->type = self::TYPE_USER;
        }
        else
        {
            throw new BadMethodCallException('First argument must be instance of User or ClassObject');
        }
        
        $this->id = $id;
        $this->newName = $newName;
        $this->requestedAt = $requestedTime;
    }
    
    /**
     * Metoda navracející aktuální jméno třídy nebo uživatele
     * @return string Stávající jméno
     */
    public function getOldName()
    {
        return $this->object->getName();
    }
    
    /**
     * Metoda navracejícící požadované jméno třídy nebo uživatele
     * @return string Požadované nové jméno
     */
    public function getNewName()
    {
        return $this->newName;
    }
    
    
    public function getRequestersEmail()
    {
        if ($this->type === self::TYPE_CLASS)
        {
            //Změna jména třídy
            return $this->object->getAdmin()['email'];
        }
        //Změna jména uživatele
        return $this->object['email'];
    }
}