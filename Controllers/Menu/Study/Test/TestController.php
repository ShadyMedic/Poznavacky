<?php
namespace Poznavacky\Controllers\Menu\Study\Test;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
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
     * Metoda nastavující hlavičku stránky a pohled
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
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

        self::$pageHeader['title'] = 'Vyzkoušet se';
        self::$pageHeader['description'] = 'Vyzkoušejte si, jak dobře znáte přírodniny v poznávačce pomocí náhodného testování';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/css.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js','js/ajaxMediator.js','js/test.js','js/reportForm.js','js/menu.js');
        self::$pageHeader['bodyId'] = 'test';

        if ($allParts)
        {
            (new Logger(true))->info('Přístup na stránku pro zkoušení ze všech částí poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}', array('groupId' => $group->getId(), 'classId' => $class->getId(), 'userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
        }
        else
        {
            (new Logger(true))->info('Přístup na stránku pro zkoušení z části s ID {partId} patřící do poznávačky s ID {groupId} patřící do třídy s ID {classId} uživatelem s ID {userId} z IP adresy {ip}', array('partId' => $part->getId(), 'groupId' => $group->getId(), 'classId' => $class->getId(), 'userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
        }
    }
}

