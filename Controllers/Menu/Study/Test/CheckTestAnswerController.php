<?php
namespace Poznavacky\Controllers\Menu\Study\Test;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;
use Poznavacky\Models\AnswerChecker;
use Poznavacky\Models\Logger;

/** 
 * Kontroler volaný pomocí AJAX, který ověřuje odpověď zadanou uživatelem na testovací stránce
 * @author Jan Štěch
 */
class CheckTestAnswerController extends Controller
{
    /**
     * Metoda načítající odpověď z $_POST a ověřuje jí proti správné odpovědi uložené v $_SESSION
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        $questionNum = $_POST['qNum'];
        $answer = $_POST['ans'];
        
        header('Content-Type: application/json');
        $checker = new AnswerChecker();
        try
        {
            $result = $checker->verify($answer, $questionNum);
        }
        catch(AccessDeniedException $e)
        {
            //Neplatné číslo otázky nebo jiná chyba při ověřování odpovědi
            (new Logger(true))->notice('Uživatel s ID {userId} přistupující do systému z IP adresy {ip} odeslal svou odpověď na obrázek číslo {questionNum} na zkoušecí stránce části/í poznávačky s ID {groupId}, avšak správná odpověď nebyla v úložišti sezení nalezena', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'questionNum' => $questionNum, 'groupId' => $_SESSION['selection']['group']->getId()));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_ERROR, $e->getMessage());
            echo $response->getResponseString();
            exit();
        }
        
        if ($result)
        {
            (new Logger(true))->info('Odpověď uživatele s ID {userId} přistupujícího do systému z IP adresy {ip} na otázku číslo {questionNum} na zkoušecí stránce části/í poznávačky s ID {groupId} byla vyhodnocena jako správná', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'questionNum' => $questionNum, 'groupId' => $_SESSION['selection']['group']->getId()));
            UserManager::getUser()->incrementGuessedPictures();
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_INFO, 'correct', array('answer' => $checker->lastSavedAnswer));
            echo $response->getResponseString();
        }
        else
        {
            (new Logger(true))->info('Odpověď uživatele s ID {userId} přistupujícího do systému z IP adresy {ip} na otázku číslo {questionNum} na zkoušecí stránce části/í poznávačky s ID {groupId} byla vyhodnocena jako nesprávná', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'questionNum' => $questionNum, 'groupId' => $_SESSION['selection']['group']->getId()));
            $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_INFO, 'wrong', array('answer' => $checker->lastSavedAnswer));
            echo $response->getResponseString();
        }
        
        //Vymaž využitou odpověď ze $_SESSION['testAnswers']
        //Tak nebude možné odpověď odeslat znovu a farmit tak uhodnuté obrázky
        unset($_SESSION['testAnswers'][$questionNum]);
        
        //Zastav zpracování skriptu, aby se nevypsaly pohledy
        exit();
    }
}

