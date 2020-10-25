<?php
/** 
 * Kontroler starající se o výpis stránky pro obnovu hesla
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
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/recoverPassword.js');
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
            
            $userId = PasswordRecoveryCodeVerificator::verifyCode($code);
            if (empty($userId))
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_RECOVER_INVALID_TOKEN, null, null, array('originalFile' => 'RecoverPasswordController.php', 'displayOnView' => 'recoverPasseword.phtml'));
            }
            
            //Získat jméno uživatele pro zobrazení na stránce
            Db::connect();
            $username = Db::fetchQuery('SELECT '.User::COLUMN_DICTIONARY['name'].' FROM '.User::TABLE_NAME.' WHERE '.User::COLUMN_DICTIONARY['id'].' = ?', array($userId), false)[User::COLUMN_DICTIONARY['name']];
            
            $this->data['username'] = $username;
        }
        catch (AccessDeniedException $e)
        {
            //Chybný kód
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, $e->getMessage());
            $this->redirect('');
        }
        
        if (isset($this->data['username']))
        {
            //Kód nalezen a uživatel identifikován
            $this->view = 'recoverPassword';
        }
    }
}