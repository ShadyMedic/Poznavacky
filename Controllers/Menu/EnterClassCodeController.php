<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Security\DataValidator;
use Poznavacky\Models\Statics\ClassManager;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use Poznavacky\Models\MessageBox;

/**
 * Kontroler zpracovávající data z formuláře pro zadání kódu od soukromé třídy na menu stránce
 * @author Jan Štěch
 */
class EnterClassCodeController extends Controller
{
    /**
     * Metoda zpracovávající data odeslaná formulářem
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        //Kontrola, zda se nejedná o demo účet
        $aChecker = new AccessChecker();
        if ($aChecker->checkDemoAccount())
        {
            (new Logger(true))->warning('Uživatel demo účtu se pokusil zadat kód pro vstup do jiné než demo třídy z IP adresy {ip}', array('ip' => $_SERVER['REMOTE_ADDR']));
            $this->redirect('error403');
        }

        $userId = UserManager::getId();

        if (!isset($_POST) || mb_strlen($_POST['code']) === 0)
        {
            //Chybně vyplněný formulář
            (new Logger(true))->notice('Uživatel s ID {userId} odeslal formulář pro zadání vstupního kódu třídy z IP adresy {ip}, avšak nevyplnil do něj údaje', array('userId' => $userId, 'ip' => $_SERVER['REMOTE_ADDR']));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Musíte vyplnit kód třídy');
            $this->redirect('menu');
        }
        
        $code = $_POST['code'];
        
        //Validace kódu
        $validator = new DataValidator();
        if (!$validator->validateClassCode($code))
        {
            (new Logger(true))->notice('Uživatel s ID {userId} odeslal formulář pro zadání vstupního kódu třídy z IP adresy {ip}, avšak zadaný kód ({code}) nebyl ve správném formátu', array('userId' => $userId, 'ip' => $_SERVER['REMOTE_ADDR'], 'code' => $code));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Vstupní kód třídy musí být tvořen čtyřmi číslicemi');
            $this->redirect('menu');
        }
        
        $classes = ClassManager::getNewClassesByAccessCode($code, $userId);
        if (count($classes) === 0)
        {
            //Se zadaným kódem se nelze dostat do žádné třídy
            (new Logger(true))->notice('Uživatel s ID {userId} odeslal vstupní kód třídy z IP adresy {ip}, avšak zadaný kód ({$code}) není možné použít k získání přístupu do žádné třídy', array('userId' => $userId, 'ip' => $_SERVER['REMOTE_ADDR'], 'code' => $code));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Zadaný kód není platný');
            $this->redirect('menu');
        }
        
        $accessedClasses = array();
        $accessedClassesIds = array();
        foreach($classes as $class)
        {
                $accessedClasses[] = $class->getName();
                $accessedClassesIds[] = $class->getId();
        }
        
		//Vypsat do zprávy pro uživatele jména tříd do kterých získal přístup uložená v $accessedClasses
		(new Logger(true))->info('Uživatel s ID {userId} odeslal vstupní kód třídy ({code}) z IP adresy {ip} a získal přístup do tříd/y s ID {classesIds}', array('userId' => $userId, 'code' => $code, 'ip' => $_SERVER['REMOTE_ADDR'], 'classesIds' => implode(', ', $accessedClassesIds)));
		$this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Získali jste přístup do následujících tříd: '.implode(', ',$accessedClasses));
        
        $this->redirect('menu');
    }
}

