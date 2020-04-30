<?php
/** 
 * @author Jan Štěch
 */
class RecoverPasswordController extends Controller
{
    /**
     * Metoda ověřující, zda je zadán platný kód pro obnovu hesla, nastavující hlavičku stránky a pohled
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        $this->pageHeader['title'] = 'Obnovit heslo';
        $this->pageHeader['description'] = 'Zapomněli jste heslo ke svému účtu? Na této stránce si jej můžete obnobit pomocí kódu, který obdržíte na e-mail.';
        $this->pageHeader['keywords'] = 'poznávačky, účet, heslo, obnova';
        $this->pageHeader['cssFile'] = 'css/css.css';
        $this->pageHeader['jsFile'] = 'js/recoverPassword.js';
        $this->pageHeader['bodyId'] = 'recoverPassword';
        
        try
        {
            //Zjištění, zda je v adrese přítomen kód pro obnovu hesla
            if (!isset($parameters[0]))
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_RECOVER_NO_TOKEN, null, null, array('originalFile' => 'RecoverPasswordController.php', 'displayOnView' => 'recoverPasseword.phtml'));
            }
            $code = $parameters[0];
            $this->data['token'] = $code;
            
            PasswordRecoveryCodeVerificator::deleteOutdatedCodes();
            $userId = PasswordRecoveryCodeVerificator::verifyCode($code);
            if (empty($userId))
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_RECOVER_INVALID_TOKEN, null, null, array('originalFile' => 'RecoverPasswordController.php', 'displayOnView' => 'recoverPasseword.phtml'));
            }
            
            //Získat jméno uživatele pro zobrazení na stránce
            Db::connect();
            $username = Db::fetchQuery('SELECT jmeno FROM uzivatele WHERE uzivatele_id = ?', array($userId), false)['jmeno'];
            
            $this->data['username'] = $username;
        }
        catch (AccessDeniedException $e)
        {
            $this->data['message'] = $e->getMessage();
            $this->view = 'recoverPasswordMessage';
        }
        catch (DatabaseException $e)
        {
            $this->data['message'] = $e->getDbInfo()['message'];
            $this->view = 'recoverPasswordMessage';
        }
        
        if (isset($this->data['username']))
        {
            //Kontrola chybových hlášek
            if (isset($_COOKIE['recoveryErrorMessage']))
            {
                $errMsg = $_COOKIE['recoveryErrorMessage'];
                
                unset($_COOKIE['recoveryErrorMessage']);
                setcookie('recoveryErrorMessage', null, -1);
            }
            else
            {
                $errMsg = '';
            }
            $this->data['recoveryErrorMessage'] = $errMsg;
            $this->view = 'recoverPassword';
        }
    }
}

