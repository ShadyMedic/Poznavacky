<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\DatabaseItems\User;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Logger;
use Poznavacky\Models\MessageBox;
use Poznavacky\Models\PasswordRecoveryCodeVerificator;
use Poznavacky\Models\Statics\Db;

/** 
 * Kontroler starající se o výpis stránky pro obnovu hesla
 * @author Jan Štěch
 */
class RecoverPasswordController extends SynchronousController
{
    /**
     * Metoda ověřující, zda je zadán platný kód pro obnovu hesla, nastavující hlavičku stránky a pohled
     * @param array $parameters Parametry pro kontroler; první element by měl obsahovat kód pro obnovu hesla
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        $this->pageHeader['title'] = 'Obnovit heslo';
        $this->pageHeader['description'] = 'Zapomněli jste heslo ke svému účtu? Na této stránce si jej můžete obnobit pomocí kódu, který obdržíte na e-mail.';
        $this->pageHeader['keywords'] = 'poznávačky, účet, heslo, obnova';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/recoverPassword.js');
        $this->pageHeader['bodyId'] = 'recover-password';

        $code = null;
        try
        {
            //Zjištění, zda je v adrese přítomen kód pro obnovu hesla
            if (!isset($parameters[0]))
            {
                (new Logger(true))->notice('Vstup na stránku pro obnovení hesla byl uživateli na IP adrese {ip} zamítnut, protože URL adresa neobsahuje kód pro obnovu hesla', array('ip' => $_SERVER['REMOTE_ADDR']));
                throw new AccessDeniedException(AccessDeniedException::REASON_RECOVER_NO_TOKEN, null, null);
            }
            $code = $parameters[0];
            $this->data['token'] = $code;
            
            $codeVerificator = new PasswordRecoveryCodeVerificator();
            $userId = $codeVerificator->verifyCode($code);
            if (empty($userId))
            {
                (new Logger(true))->notice('Kód pro obnovu hesla (hash {hash}) odeslaný z IP adresy {ip} se nejeví jako platný', array('hash' => md5($code), 'ip' => $_SERVER['REMOTE_ADDR']));
                throw new AccessDeniedException(AccessDeniedException::REASON_RECOVER_INVALID_TOKEN, null, null);
            }
            (new Logger(true))->info('Kód pro obnovu hesla (hash {hash}) odeslaný z IP adresy {ip} byl ověřen a uživateli byla umožněna změna hesla k uživatelskému účtu s ID {userId}', array('hash' => md5($code), 'ip' => $_SERVER['REMOTE_ADDR'], 'userId' => $userId));

            //Získat jméno uživatele pro zobrazení na stránce
            $username = Db::fetchQuery('SELECT '.User::COLUMN_DICTIONARY['name'].' FROM '.User::TABLE_NAME.' WHERE '.User::COLUMN_DICTIONARY['id'].' = ?', array($userId), false)[User::COLUMN_DICTIONARY['name']];
            
            $this->data['username'] = $username;
        }
        catch (AccessDeniedException $e)
        {
            //Chybný kód
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, $e->getMessage());
            $this->redirect('');
        }
        catch (DatabaseException $e)
        {
            (new Logger(true))->alert('Uživatel přistupující do systému z IP adresy {ip} odeslal kód pro obnovu hesla (hash {hash}), který se však nepodařilo ověřit kvůli chybě databáze; je možné, že se k databázi nelze připojit', array('ip' => $_SERVER['REMOTE_ADDR'], 'hash' => md5($code)));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, AccessDeniedException::REASON_UNEXPECTED);
        }

        if (isset($this->data['username']))
        {
            //Kód nalezen a uživatel identifikován
            $this->view = 'recoverPassword';
        }
    }
}

