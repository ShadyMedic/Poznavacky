<?php
namespace Poznavacky\Controllers\Menu\Study\Test;

use Poznavacky\Controllers\AjaxController;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Logger;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;

/** 
 * Kontroler volaný pomocí AJAX, který zajišťuje odeslání adresy obrázků pro testovací stránku
 * @author Jan Štěch
 */
class TestPicturesController extends AjaxController
{
    private const PICTURES_SENT_PER_REQUEST = 20;

    /**
     * Metoda odesílající daný počet náhodně zvolených obrázků ze zvolené části/přírodniny
     * Adresy jsou odeslány jako pole v JSON formátu
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see AjaxController::process()
     */
    public function process(array $parameters): void
    {
        //Kontrola přístupu již proběhla v MenuController.php

        $class = $_SESSION['selection']['class'];
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
            (new Logger(true))->warning('Uživatel s ID {userId} se pokusil získat náhodné obrázky pro zkoušecí stránku poznávačky s ID {groupId} z IP adresy {ip}, avšak zvolená poznávačka/část neobsahuje žádné přírodniny', array('userId' => UserManager::getId(), 'groupId' => $group->getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            header('HTTP/1.0 400 Bad Request');
            exit();
        }

        //Získání objektů obrázků
        $pictures = array();
        try
        {
            if (isset($_SESSION['selection']['part']))
            {
                $part = $_SESSION['selection']['part'];
                $pictures = $part->getRandomPictures(self::PICTURES_SENT_PER_REQUEST);
            }
            else
            {
                $pictures = $group->getRandomPictures(self::PICTURES_SENT_PER_REQUEST);
            }
        }
        catch (DatabaseException $e)
        {
            (new Logger(true))->alert('Uživatel s ID {userId} zažádal o náhodné obrázky pro zkoušecí stránku poznávačky s ID {groupId} z IP adresy {ip}, avšak při jejich načítání došlo k chybě databáze; pokud toto není ojedinělá chyba, je možné, že tato část systému nefunguje nikomu; chybová hláška: {exception}', array('userId' => UserManager::getId(), 'groupId' => $group->getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'exception' => $e));
        }

        //Vymazání předchozích odpovědí
        unset($_SESSION['testAnswers']);
        
        //Uložení nových odpovědí do $_SESSION a stavba dvourozměrného pole k odeslání
        $picturesArr = array();
        for ($i = 0; $i < count($pictures); $i++)
        {
            $picturesArr[] = array('num' => $i, 'url' => $pictures[$i]->getSrc());
            $_SESSION['testAnswers'][$i] = $pictures[$i]->getNatural()->getName();
        }
        
        //Odeslání dvourozměrného pole s čísly otázek a adresami obrázků
        (new Logger(true))->info('K uživateli s ID {userId} přistupujícímu do systému z IP adresy {ip} byly odeslány náhodné obrázky pro zkoušecí stránku části/částí poznávačky s ID {groupId} patřící do třídy s ID {classId}', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'groupId' => $group->getId(), 'classId' => $class->getId()));
        header('Content-Type: application/json');
        $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, '', array('pictures' => $picturesArr));
        echo $response->getResponseString();
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}

