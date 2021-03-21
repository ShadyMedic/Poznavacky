<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\AjaxController;
use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Security\DataValidator;
use Poznavacky\Models\Statics\ClassManager;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\Logger;

/**
 * Kontroler zpracovávající data z formuláře pro zadání kódu od soukromé třídy na menu stránce
 * @author Jan Štěch
 */
class EnterClassCodeController extends AjaxController
{
    /**
     * Metoda zpracovávající data odeslaná formulářem
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        $userId = UserManager::getId();
        if (!isset($_POST) || mb_strlen($_POST['code']) === 0)
        {
            //Chybně vyplněný formulář
            (new Logger(true))->notice('Uživatel s ID {userId} odeslal formulář pro zadání vstupního kódu třídy z IP adresy {ip}, avšak nevyplnil do něj údaje', array('userId' => $userId, 'ip' => $_SERVER['REMOTE_ADDR']));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, 'Musíte vyplnit kód třídy');
            echo $response->getResponseString();
            return;
        }

        $code = $_POST['code'];

        //Validace kódu
        $validator = new DataValidator();
        if (!$validator->validateClassCode($code))
        {
            (new Logger(true))->notice('Uživatel s ID {userId} odeslal formulář pro zadání vstupního kódu třídy z IP adresy {ip}, avšak zadaný kód ({code}) nebyl ve správném formátu', array('userId' => $userId, 'ip' => $_SERVER['REMOTE_ADDR'], 'code' => $code));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, 'Vstupní kód třídy musí být tvořen čtyřmi číslicemi');
            echo $response->getResponseString();
            return;
        }

        $classes = ClassManager::getNewClassesByAccessCode($code, $userId);
        if (count($classes) === 0)
        {
            //Se zadaným kódem se nelze dostat do žádné třídy
            (new Logger(true))->notice('Uživatel s ID {userId} odeslal vstupní kód třídy z IP adresy {ip}, avšak zadaný kód ({$code}) není možné použít k získání přístupu do žádné třídy', array('userId' => $userId, 'ip' => $_SERVER['REMOTE_ADDR'], 'code' => $code));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_WARNING, 'Zadaný kód není platný');
            echo $response->getResponseString();
            return;
        }

        $accessedClasses = array();
        $accessedClassesIds = array();
        $accessedClassInformation = array();
        foreach($classes as $class)
        {
            if ($class->addMember($userId))
            {
                $accessedClasses[] = $class->getName();
                $accessedClassesIds[] = $class->getId();
                $accessedClassInformation[] = array(
                    'name' => $class->getName(),
                    'url' => $class->getUrl(),
                    'groupsCount' => $class->getGroupsCount()
                );
            }
        }

		//Vypsat do zprávy pro uživatele jména tříd do kterých získal přístup uložená v $accessedClasses
		(new Logger(true))->info('Uživatel s ID {userId} odeslal vstupní kód třídy ({code}) z IP adresy {ip} a získal přístup do tříd/y s ID {classesIds}', array('userId' => $userId, 'code' => $code, 'ip' => $_SERVER['REMOTE_ADDR'], 'classesIds' => implode(', ', $accessedClassesIds)));
		$response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, 'Získali jste přístup do následujících tříd: '.implode(', ',$accessedClasses), array('accessedClassesInfo' => $accessedClassInformation));
		echo $response->getResponseString();
    }
}

