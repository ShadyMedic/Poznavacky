<?php
namespace Poznavacky\Controllers\Menu\Study\Learn;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Logger;
use Poznavacky\Models\MessageBox;
use Poznavacky\Models\Statics\UserManager;

/** 
 * Kontroler starající se o výpis stránky pro učení se
 * @author Jan Štěch
 */
class LearnController extends SynchronousController
{

    /**
     * Metoda ověřující, zda má uživatel do třídy přístup a nastavující hlavičku stránky a pohled
     * @param array $parameters Parametry pro zpracování kontrolerem, může být prázdné, nebo obsahovat URL název kontroleru, který má být zavolán tímto kontrolerem
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
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
        
        //Kontrola přístupu již proběhla v MenuCotnroller.php

        //Kontrola přítomnosti přírodnin
        if (
            ($allParts && count($group->getNaturals()) === 0) ||
            (!$allParts && $part->getNaturalsCount() === 0)
        )
        {
            //Žádné přírodniny
            (new Logger(true))->warning('Uživatel s ID {userId} se pokusil přistoupit na stránku pro učení části/í poznávačky s ID {groupId} patřící do třídy s ID {classId} z IP adresy {ip}, ačkoliv tato poznávačka/část neobsahuje žádné přírodniny', array('userId' => UserManager::getId(), 'groupId' => $group->getId(), 'classId' => $class->getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, "V této části nebo poznávačce nejsou zatím přidané žádné přírodniny");
            $this->redirect('menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl());
        }

        self::$pageHeader['title'] = 'Učit se';
        self::$pageHeader['description'] = 'Učte se na poznávačku podle svého vlastního tempa';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/css.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js','js/ajaxMediator.js','js/learn.js','js/reportForm.js', 'js/menu.js');
        self::$pageHeader['bodyId'] = 'learn';

        if ($allParts)
        {
            self::$data['navigationBar'] = array(
                0 => array(
                    'text' => self::$pageHeader['title'],
                    'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl().'/learn'
                )
            );

            self::$data['naturals'] = $group->getNaturals();
            (new Logger(true))->info('Přístup na stránku pro učení všech částí poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}', array('groupId' => $group->getId(), 'classId' => $class->getId(), 'userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
        }
        else
        {
            self::$data['navigationBar'] = array(
                0 => array(
                    'text' => self::$pageHeader['title'],
                    'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl().'/'.$_SESSION['selection']['part']->getUrl().'/learn'
                )
            );

            self::$data['naturals'] = $part->getNaturals();
            (new Logger(true))->info('Přístup na stránku pro učení části s ID {partId} patřící do poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}', array('partId' => $part->getId(), 'groupId' => $group->getId(), 'classId' => $class->getId(), 'userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
        }

        $controllerName = "nonexistant-controller";
        if (isset($parameters[0])){ $controllerName = $this->kebabToCamelCase($parameters[0]).self::CONTROLLER_EXTENSION; }
        $pathToController = $this->classExists($controllerName);
        if ($pathToController)
        {
            //URL obsajuje požadavek na další kontroler používaný na learn stránce
            $this->controllerToCall = new $pathToController();
            $this->controllerToCall->process($parameters);
            self::$data['navigationBar'] = array_merge(self::$data['navigationBar'], $this->controllerToCall::data['navigationBar']);
        }
        
        $this->view = 'learn';
    }
}

