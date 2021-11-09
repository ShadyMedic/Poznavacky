<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\DatabaseItems\User;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\Logger;
use Poznavacky\Models\MessageBox;
use Poznavacky\Models\PasswordRecoveryCodeVerificator;

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
        $code = null;
        try {
            //Zjištění, zda je v adrese přítomen kód pro obnovu hesla
            if (!isset($parameters[0])) {
                (new Logger(true))->notice('Vstup na stránku pro obnovení hesla byl uživateli na IP adrese {ip} zamítnut, protože URL adresa neobsahuje kód pro obnovu hesla',
                    array('ip' => $_SERVER['REMOTE_ADDR']));
                throw new AccessDeniedException(AccessDeniedException::REASON_RECOVER_NO_TOKEN, null, null);
            }
            $code = $parameters[0];
            self::$data['token'] = $code;
            
            $codeVerificator = new PasswordRecoveryCodeVerificator();
            $userId = $codeVerificator->verifyCode($code);
            if (empty($userId)) {
                (new Logger(true))->notice('Kód pro obnovu hesla (hash {hash}) odeslaný z IP adresy {ip} se nejeví jako platný',
                    array('hash' => md5($code), 'ip' => $_SERVER['REMOTE_ADDR']));
                throw new AccessDeniedException(AccessDeniedException::REASON_RECOVER_INVALID_TOKEN, null, null);
            }
            (new Logger(true))->info('Kód pro obnovu hesla (hash {hash}) odeslaný z IP adresy {ip} byl ověřen a uživateli byla umožněna změna hesla k uživatelskému účtu s ID {userId}',
                array('hash' => md5($code), 'ip' => $_SERVER['REMOTE_ADDR'], 'userId' => $userId));
            
            //Získat jméno uživatele pro zobrazení na stránce
            $username = Db::fetchQuery('SELECT '.User::COLUMN_DICTIONARY['name'].' FROM '.User::TABLE_NAME.' WHERE '.
                                       User::COLUMN_DICTIONARY['id'].' = ?', array($userId),
                false)[User::COLUMN_DICTIONARY['name']];
            
            self::$data['username'] = $username;
        } catch (AccessDeniedException $e) {
            //Chybný kód
            (new Logger(true))->alert('Uživatel přistupující do systému z IP adresy {ip} odeslal kód pro obnovu hesla (hash {hash}), který však zřejmě nebyl platný',
                array('ip' => $_SERVER['REMOTE_ADDR'], 'hash' => md5($code)));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, $e->getMessage());
            $this->redirect('');
        } catch (DatabaseException $e) {
            (new Logger(true))->alert('Uživatel přistupující do systému z IP adresy {ip} odeslal kód pro obnovu hesla (hash {hash}), který se však nepodařilo ověřit kvůli chybě databáze; je možné, že se k databázi nelze připojit',
                array('ip' => $_SERVER['REMOTE_ADDR'], 'hash' => md5($code)));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, AccessDeniedException::REASON_UNEXPECTED);
            $this->redirect('');
        }
        
        //Kontrola kódu v pořádku
        
        self::$pageHeader['title'] = 'Obnovit heslo';
        self::$pageHeader['description'] = 'Zapomněli jste heslo ke svému účtu? Na této stránce si jej můžete obnobit pomocí kódu, který obdržíte na e-mail.';
        self::$pageHeader['keywords'] = 'poznávačky, účet, heslo, obnova';
        self::$pageHeader['cssFiles'] = array('css/passwordRecovery.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/ajaxMediator.js', 'js/recoverPassword.js');
        self::$pageHeader['bodyId'] = 'recover-password';
        
    }
}

