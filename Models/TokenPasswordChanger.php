<?php
/** 
 * Třída umožňující změnu hesla uživatele po odeslání formuláře na recover-password stránce
 * @author Jan Štěch
 */
class TokenPasswordChanger
{
    private $token;
    private $pass;
    private $repass;
    private $userId;
    private $verified = false;
    private $checked = false;
    
    /**
     * Konstruktor nastavující privátní vlastnosti objektu které jsou později ověřovány
     * @param string $token Kód pro obnovu hesla odeslaný z formuláře na password-recovery stránce
     * @param string $pass Heslo odeslané z formuláře
     * @param string $repass Heslo znovu odeslané z formuláře
     */
    public function __construct(string $token, string $pass, string $repass)
    {
        $this->token = $token;
        $this->pass = $pass;
        $this->repass = $repass;
    }
    
    /**
     * Metoda ověřující platnost kódu pro obnovení hesla a ukládající ID uživatele s jehož účtem je svázán
     * @throws AccessDeniedException Pokud není kód v databázi nalezen
     */
    public function verifyToken()
    {
        PasswordRecoveryCodeVerificator::deleteOutdatedCodes();
        $this->userId = PasswordRecoveryCodeVerificator::verifyCode($this->token);
        if (empty($this->userId))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_RECOVER_INVALID_TOKEN);
        }
        $this->verified = true;
    }
    
    /**
     * Metoda odstraňující použitý kód pro obnovu hesla z databáze
     */
    public function devalueToken()
    {
        PasswordRecoveryCodeVerificator::deleteCode($this->token);
    }
    
    /**
     * Metoda ověřující, zda je možné zadané heslo použít
     * @throws AccessDeniedException Pokud se hesla neshodují
     */
    function checkPasswords()
    {
        $validator = new DataValidator();
        try
        {
            $validator->checkLength($this->pass, 6, 31, 1);
            $validator->checkCharacters($this->pass, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ {}()[]#:;^,.?!|_`~@$%/+-*=\"\'', 1);
        }
        catch (RangeException $e)
        {
            if ($e->getMessage() === 'long')
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_PASSWORD_TOO_LONG, null, null, array('originalFile' => 'TokenPasswordChanger.php', 'displayOnView' => 'recoverPassword.phtml'));
            }
            else if ($e->getMessage() === 'short')
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_PASSWORD_TOO_SHORT, null, null, array('originalFile' => 'TokenPasswordChanger.php', 'displayOnView' => 'recoverPassword.phtml'));
            }
        }
        catch (InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_PASSWORD_INVALID_CHARACTERS, null, null, array('originalFile' => 'TokenPasswordChanger.php', 'displayOnView' => 'recoverPassword.phtml'));
        }
        
        if ($this->pass !== $this->repass)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_DIFFERENT_PASSWORDS, null, null, array('originalFile' => 'TokenPasswordChanger.php', 'displayOnView' => 'recoverPassword.phtml'));
        }
        $this->checked = true;
    }
    
    /**
     * Metoda měnící heslo uživatele
     * @throws RuntimeException Pokud zatím nebyl ověřen kód pro obnovu hesla nebo platnost hesel
     * @return boolean TRUE, pokud je heslo úspěšně změněno
     */
    function changePassword()
    {
        if (!($this->verified && $this->checked))
        {
            throw new RuntimeException('Zatím nebyl ověřen kód pro obnovu hesla nebo platnost hesel. Pokud toto čtete, kontaktujte prosím správce');
        }
        Db::connect();
        Db::executeQuery('UPDATE '.User::TABLE_NAME.' SET '.LoggedUser::COLUMN_DICTIONARY['hash'].' = ? WHERE '.LoggedUser::COLUMN_DICTIONARY['id'].' = ?', array(password_hash($this->pass, PASSWORD_DEFAULT), $this->userId));
        return true;
    }
}

