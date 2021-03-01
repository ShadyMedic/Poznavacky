<?php
namespace Poznavacky\Controllers\Menu\Study\Learn;

use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\DatabaseItems\Natural;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\Logger;

/** 
 * Kontroler volaný pomocí AJAX, který zajišťuje odeslání adresy obrázků pro učební stránku
 * @author Jan Štěch
 */
class LearnPicturesController extends AjaxController
{

    /**
     * Metoda přijímající název přírodniny skrz $_POST a získávající zdroje všech jejích obrázků z databáze
     * Adresy jsou odeslány jako pole v JSON formátu
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see AjaxController::process()
     */
    public function process(array $parameters): void
    {
        //Kontrola přístupu již proběhla v MenuController.php

        $group = $_SESSION['selection']['group'];
        $part = null;
        if (isset($_SESSION['selection']['part']))
        {
            $part = $_SESSION['selection']['part'];
            $allParts = false;
        }
        else
        {
            $allParts = true;
        }

        //Kontrola přítomnosti přírodnin
        if (
            ($allParts && count($group->getNaturals()) === 0) ||
            (!$allParts && $part->getNaturalsCount() === 0)
        )
        {
            //Žádné přírodniny
            (new Logger(true))->warning('Uživatel s ID {userId} se pokusil získat obrázek pro učební stránku poznávačky s ID {groupId} z IP adresy {ip}, avšak zvolená poznávačka/část neobsahuje žádné přírodniny', array('userId' => UserManager::getId(), 'groupId' => $group->getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            header('HTTP/1.0 400 Bad Request');
            exit();
        }

        $naturalName = urldecode($_GET['natural']);
        
        $natural = new Natural(false);
        $class = $_SESSION['selection']['class'];
        $natural->initialize($naturalName, null, null, $class);
        try { $pictures = $natural->getPictures(); }
        catch (DatabaseException $e)
        {
            (new Logger(true))->alert('Uživatel s ID {userId} zažádal o obrázky přírodniny s ID {naturalId} pro učební stránku poznávačky s ID {groupId} z IP adresy {ip}, avšak při jejich načítání došlo k chybě databáze; pokud toto není ojedinělá chyba, je možné, že tato část systému nefunguje nikomu; chybová hláška: {exception}', array('userId' => UserManager::getId(), 'naturalId' => $natural->getId(), 'groupId' => $group->getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'exception' => $e));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, AccessDeniedException::REASON_UNEXPECTED);
            echo $response->getResponseString();
            exit();
        }
        
        $picturesArr = array();
        foreach ($pictures as $picture)
        {
            $picturesArr[] = $picture->getSrc();
        }

        (new Logger(true))->info('K uživateli s ID {userId} přistupujícímu do systému z IP adresy {ip} byly odeslány obrázky přírodniny s ID {naturalId} pro učební stránku', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'naturalId' => $natural->getId()));
        header('Content-Type: application/json');
        $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, '', array('pictures' => $picturesArr));
        echo $response->getResponseString();
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}

