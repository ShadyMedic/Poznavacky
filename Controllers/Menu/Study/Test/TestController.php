<?php
namespace Poznavacky\Controllers\Menu\Study\Test;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use Poznavacky\Models\MessageBox;

/** 
 * Kontroler starající se o výpis stránky pro testování
 * @author Jan Štěch
 */
class TestController extends SynchronousController
{
    /**
     * Metoda ověřující, zda má uživatel do třídy přístup a nastavující hlavičku stránky a pohled
     * @param array $parameters Pole parametrů pro zpracování kontrolerem, zde může jako první prvek obsahovat URL název dalšího kontroleru, kterému má tento kontroler předat řízení
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        //Kontrola přístupu již proběhla v MenuController.php

        $class = $_SESSION['selection']['group'];
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
            (new Logger(true))->warning('Uživatel s ID {userId} se pokusil přistoupit na stránku pro zkoušení z části/í poznávačky s ID {groupId} patřící do třídy s ID {classId} z IP adresy {ip}, ačkoliv tato poznávačka/část neobsahuje žádné přírodniny', array('userId' => UserManager::getId(), 'groupId' => $group->getId(), 'classId' => $class->getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, "V této části nebo poznávačce nejsou zatím přidané žádné přírodniny");
            $this->redirect('menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl());
        }

        $this->pageHeader['title'] = 'Vyzkoušet se';
        $this->pageHeader['description'] = 'Vyzkoušejte si, jak dobře znáte přírodniny v poznávačce pomocí náhodného testování';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/ajaxMediator.js','js/test.js','js/reportForm.js','js/menu.js');
        $this->pageHeader['bodyId'] = 'test';

        if ($allParts)
        {
            $this->data['navigationBar'] = array(
                0 => array(
                    'text' => $this->pageHeader['title'],
                    'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl().'/test'
                )
            );

            (new Logger(true))->info('Přístup na stránku pro zkoušení ze všech částí poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}', array('groupId' => $group->getId(), 'classId' => $class->getId(), 'userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
        }
        else
        {
            $this->data['navigationBar'] = array(
                0 => array(
                    'text' => $this->pageHeader['title'],
                    'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl().'/'.$_SESSION['selection']['part']->getUrl().'/test'
                )
            );

            (new Logger(true))->info('Přístup na stránku pro zkoušení z části s ID {partId} patřící do poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}', array('partId' => $part->getId(), 'groupId' => $group->getId(), 'classId' => $class->getId(), 'userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
        }

        $controllerName = "nonexistant-controller";
        if (isset($parameters[0])){ $controllerName = $this->kebabToCamelCase($parameters[0]).self::CONTROLLER_EXTENSION; }
        $pathToController = $this->controllerExists($controllerName);
        if ($pathToController)
        {
            //URL obsajuje požadavek na další kontroler používaný na test stránce
            $this->controllerToCall = new $pathToController();
            $this->controllerToCall->process($parameters);
            
            $this->pageHeader['title'] = $this->controllerToCall->pageHeader['title'];
            $this->pageHeader['description'] = $this->controllerToCall->pageHeader['description'];
            $this->pageHeader['keywords'] = $this->controllerToCall->pageHeader['keywords'];
            $this->pageHeader['cssFiles'] = $this->controllerToCall->pageHeader['cssFiles'];
            $this->pageHeader['jsFiles'] = $this->controllerToCall->pageHeader['jsFiles'];
            $this->pageHeader['bodyId'] = $this->controllerToCall->pageHeader['bodyId'];
            $this->data['navigationBar'] = array_merge($this->data['navigationBar'], $this->controllerToCall->data['navigationBar']);
        }
        $this->view = 'test';
    }
}

