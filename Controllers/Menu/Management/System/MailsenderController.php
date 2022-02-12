<?php
namespace Poznavacky\Controllers\Menu\Management\System;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Administration;
use Poznavacky\Models\Logger;

/**
 * Kontroler starající se o výpis stránky pro odesílání e-mailů správcům služby
 * @author Jan Štěch
 */
class MailsenderController extends SynchronousController
{

    /**
     * Metoda nastavující hlavičku stránky a pohled
     * @param array $parameters Pole parametrů pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @throws DatabaseException
     * @see SynchronousController::process()
     */
    function process(array $parameters): void
    {
        $administration = new Administration();
        (new Logger(true))->info('Přístup na stránku pro odesílání e-mailů systémovým administrátorem s ID {userId} z IP adresy {ip}',
            array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));

        self::$data['loggedAdminName'] = UserManager::getName();

        self::$pageHeader['title'] = 'Odeslat e-mail';
        self::$pageHeader['description'] = 'Nástroj pro administrátory služby umožňující odesílání e-mailů z doménové schránky.';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/private.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/ajaxMediator.js', 'js/administrate.js', 'js/mailsender.js');
        self::$pageHeader['bodyId'] = 'mailsender';
    }
}

