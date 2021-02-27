<?php
namespace Poznavacky\Controllers\Menu\Study\AddPictures;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use Poznavacky\Models\MessageBox;

/**
 * Kontroler starající se o výpis stránky pro přidání obrázků
 * @author Jan Štěch
 */
class AddPicturesController extends Controller
{

    /**
     * Metoda ověřující, zda má uživatel do třídy přístup a nastavující hlavičku stránky a pohled
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see Controller::process()
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
            (new Logger(true))->warning('Uživatel s ID {userId} se pokusil přistoupit na stránku pro přidávání obrázků do části/í poznávačky s ID {groupId} patřící do třídy s ID {classId} z IP adresy {ip}, ačkoliv tato poznávačka neobsahuje žádné přírodniny', array('userId' => UserManager::getId(), 'groupId' => $group->getId(), 'classId' => $class->getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, "V této části nebo poznávačce nejsou zatím přidané žádné přírodniny");
            $this->redirect('menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl());
        }

        $this->pageHeader['title'] = 'Přidat obrázky';
        $this->pageHeader['description'] = 'Přidávejte obrázky do své poznávačky, aby se z nich mohli učit všichni členové třídy';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/ajaxMediator.js','js/addPictures.js', 'js/menu.js');
        $this->pageHeader['bodyId'] = 'add-pictures';
        
        if ($allParts)
        {
            $this->data['navigationBar'] = array(
                0 => array(
                    'text' => $this->pageHeader['title'],
                    'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl().'/addPictures'
                )
            );

            $this->data['naturals'] = $group->getNaturals();
            (new Logger(true))->info('Přístup na stránku pro přidávání obrázků do všech částí poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}', array('groupId' => $group->getId(), 'classId' => $class->getId(), 'userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
        }
        else
        {
            $this->data['navigationBar'] = array(
                0 => array(
                    'text' => $this->pageHeader['title'],
                    'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl().'/'.$_SESSION['selection']['part']->getUrl().'/addPictures'
                )
            );

            $this->data['naturals'] = $part->getNaturals();
            (new Logger(true))->info('Přístup na stránku pro přidávání obrázků do části s ID {partId} patřící do poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}', array('partId' => $part->getId(), 'groupId' => $group->getId(), 'classId' => $class->getId(), 'userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
        }
        
        $this->data['returnUrl'] = 'menu/'.$class->getUrl().'/'.$group->getUrl();
        
        $this->view = 'addPictures';
    }
}