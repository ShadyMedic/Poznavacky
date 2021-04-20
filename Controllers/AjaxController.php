<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\Logger;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;

/**
 * Obecný kontroler pro zpracovávání AJAX požadavků
 * Mateřská třída všech AJAX kotntrolerů
 */
abstract class AjaxController implements ControllerInterface
{
    
    /**
     * Kontroler ověřující, zda je požadavek, který spustil běh skriptu AJAX
     * Instance AJAX kontrolerů je možné vytvořit pouze v případě, že je požadavek asynchroní
     * V opačném případě je odeslán header 400 Bad Request a běh skriptu je ukončen
     */
    public function __construct()
    {
        //Kontrola, zda byl tento kontroler zavolán jako AJAX
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            $aChecker = new AccessChecker();
            if ($aChecker->checkUser()) {
                (new Logger(true))->warning('Uživatel s ID {userId} se pokusil přistoupit k AJAX kontroleru {controllerName} z IP adresy {ip} jinak než pomocí AJAX požadavku',
                    array(
                        'userId' => UserManager::getId(),
                        'controllerName' => get_class($this),
                        'ip' => $_SERVER['REMOTE_ADDR']
                    ));
            } else {
                (new Logger(true))->warning('Nepřihlášený uživatel se pokusil přistoupit k AJAX kontroleru {controllerName} z IP adresy {ip} jinak než pomocí AJAX požadavku',
                    array('controllerName' => get_class($this), 'ip' => $_SERVER['REMOTE_ADDR']));
            }
            
            header('HTTP/1.0 400 Bad Request');
            exit();
        }
    }
}

