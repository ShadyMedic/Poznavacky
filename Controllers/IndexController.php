<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Processors\LoginUser;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use Poznavacky\Models\MessageBox;

/** 
 * Kontroler starající se o vypsání úvodní stránky webu
 * @author Jan Štěch
 */
class IndexController extends SynchronousController
{

    /**
     * Metoda nastavující hlavičku stránky a pohled k zobrazení
     * @param array $parameters Parametry pro kontroler (nevyužíváno)
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        //Kontrola, zda již uživatel není přihlášen
        $aChecker = new AccessChecker();
        if ($aChecker->checkUser())
        {
            //Uživatel je již přihlášen
            (new Logger(true))->info('Přesměrovávání uživatele s ID {userId} do systému z index stránky, jelikož již je přihlášen (IP: {ip})', array('userId'=> UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            $this->redirect('menu');
        }
        
        //Kontrola automatického přihlášení
        if (isset($_COOKIE['instantLogin']))
        {
            try
            {
                $userLogger = new LoginUser();
                $userLogger->processCookieLogin($_COOKIE['instantLogin']);
                
                //Přihlášení proběhlo úspěšně
                (new Logger(true))->info('Přesměrování uživatele s ID {userId} do systému z index stránky díky úspěšnému oveření kódu pro okamžité přihlášení (IP: {ip})', array('userId'=> UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
                $this->redirect('menu');
            }
            catch (AccessDeniedException $e)
            {
                //Kód nebyl platný
                (new Logger(true))->warning('Okamžité přihlášení uživatele s ID {userId} selhalo, protože poskytnutý kód pro okamžité přihlášení nebyl platný (IP: {ip})', array('ip' => $_SERVER['REMOTE_ADDR']));
                $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, $e->getMessage());
                
                //Vymaž cookie s neplatným kódem
                setcookie('instantLogin', null, -1);
                unset($_COOKIE['instantLogin']);
            }
            catch (DatabaseException $e)
            {
                //Kód se nepodařilo ověřit kvůli chybě při práci s databází
                (new Logger(true))->critical('Kód pro okamžité přihlášení (hash {hash}) uživatele přihlašujícího se z IP adresy {ip} se nepodařilo ověřit kvůli chybě při přáci s databází; je možné že se není možné vůbec připojit k databázi', array('hash' => $_COOKIE['instantLogin'], 'ip' => $_SERVER['REMOTE_ADDR']));
            }
        }

        (new Logger(true))->info('Přístup na stránku index z IP adresy {ip}', array('ip' => $_SERVER['REMOTE_ADDR']));

        $this->pageHeader['title'] = 'Poznávačky';
        $this->pageHeader['description'] = 'Čeká vás poznávačka z biologie? Není lepší způsob, jak se na ni naučit, než použitím této webové aplikace. Vytvořte si vlastní poznávačku, společně do ní přidávejte obrázky, učte se z nich a nechte si generovat náhodné testy.';
        $this->pageHeader['keywords'] = 'poznávačky, biologie, příroda, poznávačka, přírodopis, přírodověda, test, výuka, naučit, učit, testy, učení';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/ajaxMediator.js','js/index.js');
        $this->pageHeader['bodyId'] = 'index';
        
        $this->view = 'index';
    }
}

