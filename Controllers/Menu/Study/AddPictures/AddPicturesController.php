<?php
namespace Poznavacky\Controllers\Menu\Study\AddPictures;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use Poznavacky\Models\MessageBox;

/**
 * Kontroler starající se o výpis stránky pro přidání obrázků
 * @author Jan Štěch
 */
class AddPicturesController extends SynchronousController
{

    /**
     * Metoda ověřující, zda má uživatel do třídy přístup a nastavující hlavičku stránky a pohled
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
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

        //Kontrola přístupu proběhla již v MenuController.php

        //Kontrola přítomnosti přírodnin
        if (
            ($allParts && count($group->getNaturals()) === 0) ||
            (!$allParts && $part->getNaturalsCount() === 0)
        )
        {
            //Žádné přírodniny
            (new Logger(true))->warning('Uživatel s ID {userId} se pokusil přistoupit na stránku pro přidávání obrázků do části/í poznávačky s ID {groupId} patřící do třídy s ID {classId} z IP adresy {ip}, ačkoliv tato poznávačka/část neobsahuje žádné přírodniny', array('userId' => UserManager::getId(), 'groupId' => $group->getId(), 'classId' => $class->getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, "V této části nebo poznávačce nejsou zatím přidané žádné přírodniny");
            $this->redirect('menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl());
        }

        self::$pageHeader['title'] = 'Přidat obrázky';
        self::$pageHeader['description'] = 'Přidávejte obrázky do své poznávačky, aby se z nich mohli učit všichni členové třídy';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/css.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js','js/ajaxMediator.js','js/addPictures.js', 'js/menu.js');
        self::$pageHeader['bodyId'] = 'add-pictures';
        
        if ($allParts)
        {
            self::$data['naturals'] = $group->getNaturals();
            (new Logger(true))->info('Přístup na stránku pro přidávání obrázků do všech částí poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}', array('groupId' => $group->getId(), 'classId' => $class->getId(), 'userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
        }
        else
        {
            self::$data['naturals'] = $part->getNaturals();
            (new Logger(true))->info('Přístup na stránku pro přidávání obrázků do části s ID {partId} patřící do poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}', array('partId' => $part->getId(), 'groupId' => $group->getId(), 'classId' => $class->getId(), 'userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
        }
    }
}

