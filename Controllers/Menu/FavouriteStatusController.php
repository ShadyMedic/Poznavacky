<?php

namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Logger;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;

/**
 * Kontroler zpracovávající požadavek na označení nějaké třídy jako oblíbené nebo odebrání takového označení na menu stránce
 * @author Jan Štěch
 */
class FavouriteStatusController extends AjaxController
{

    /**
     * Metoda zpracovávající požadavek na označené nebo odznačení třídy jako oblíbené
     * @param array $parameters Parametry ke zpracování, prvním prvkem pole musí být URL třídy, kterou označujeme nebo
     * odznačujeme jako oblíbenou, druhým řetězec "favourite" nebo "unfavourite", podle prováděné akce
     * @throws DatabaseException
     * @see AjaxController::process()
     */
    function process(array $parameters): void
    {
        //Validace odeslaných dat
        if (!isset($parameters) || count($parameters) !== 2 || !in_array($parameters[1], array('favourite', 'unfavourite'))) {
            //Jsou odeslána neplatná data v důsledku manipulace s HTML dokumentem
            (new Logger())->warning('Uživatel s ID {userId} odeslal požadavek na stránku pro označení nebo odznačení třídy jako oblíbené z IP adresy {ip}, avšak odeslaná data nebyla ve správném formátu',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, 'Neplatná odpověď nebo neplatná třída');
            echo $response->getResponseString();
            return;
        }

        $classUrl = $parameters[0];
        $favourite = ($parameters[1] === "favourite");

        //Kontrola, zda má uživatel do třídy přístup (sice by teoreticky ničemu neškodilo, kdyby si označoval třídy,
        //které nevidí v senzamu tříd a do kterých nemá přístup, ale není to moc intuitivní z hlediska budoucího vývoje)
        $aChecker = new AccessChecker();
        $class = new ClassObject(false);
        $class->initialize(null, $parameters[0]);
        if (!$class->checkAccess(UserManager::getId())) {
            //Přihlášený uživatel nemá do třídy přístup
            (new Logger())->warning('Uživatel s ID {userId} se pokusil {action} třídu s URL {classUrl} jako oblíbenou z IP adresy {ip}, avšak nemá do této třídy přístup',
                array('userId' => UserManager::getId(), 'action' => $favourite ? 'označit' : 'odznačit', 'classUrl' => $classUrl, 'ip' => $_SERVER['REMOTE_ADDR']));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR,
                'Tuto třídu nemůžeš označit nebo odznačit jako oblíbenou, protože do ní nemáš přístup.');
            echo $response->getResponseString();
            return;
        }

        if ($favourite) {
            //Označit jako oblíbenou
            UserManager::getUser()->markFavouriteClass($class);
            (new Logger())->info('Uživatel s ID {userId} si označil třídu s ID {classId} jako oblíbenou z IP adresy {ip}',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $class->getId(),
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS,
                'Třída byla označena jako oblíbená.');
        } else {
            //Odznačit jako oblíbenou
            UserManager::getUser()->unmarkFavouriteClass($class);
            (new Logger())->info('Uživatel s ID {userId} si odznačil třídu s ID {classId} jako oblíbenou z IP adresy {ip}',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $class->getId(),
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Třída byla odznačena jako oblíbená.');
        }

        echo $response->getResponseString();
    }
}