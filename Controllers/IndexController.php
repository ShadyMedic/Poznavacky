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
        if ($aChecker->checkUser()) {
            //Uživatel je již přihlášen
            $lastFolderUrl = UserManager::getOtherInformation()['lastMenuTableUrl'];
            if (empty($lastFolderUrl)) {
                $url = 'menu';
            } else {
                $url = 'menu/'.$lastFolderUrl;
            }
            (new Logger())->info('Přesměrovávání uživatele s ID {userId} do systému z index stránky, jelikož již je přihlášen (IP: {ip})',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            $this->redirect($url);
        }
        
        //Kontrola automatického přihlášení
        if (isset($_COOKIE['instantLogin'])) {
            try {
                $userLogger = new LoginUser();
                $userLogger->processCookieLogin($_COOKIE['instantLogin']);
                
                //Přihlášení proběhlo úspěšně
                (new Logger())->info('Přesměrování uživatele s ID {userId} do systému z index stránky díky úspěšnému oveření kódu pro okamžité přihlášení (IP: {ip})',
                    array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
                $this->redirect('menu');
            } catch (AccessDeniedException $e) {
                //Kód nebyl platný
                (new Logger())->warning('Okamžité přihlášení uživatele z IP adresy {ip} selhalo, protože poskytnutý kód pro okamžité přihlášení nebyl platný',
                    array('ip' => $_SERVER['REMOTE_ADDR']));
                $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, $e->getMessage());
                
                //Vymaž cookie s neplatným kódem
                setcookie('instantLogin', null, -1);
                unset($_COOKIE['instantLogin']);
            } catch (DatabaseException $e) {
                //Kód se nepodařilo ověřit kvůli chybě při práci s databází
                (new Logger())->critical('Kód pro okamžité přihlášení (hash {hash}) uživatele přihlašujícího se z IP adresy {ip} se nepodařilo ověřit kvůli chybě při přáci s databází; je možné že se není možné vůbec připojit k databázi',
                    array('hash' => $_COOKIE['instantLogin'], 'ip' => $_SERVER['REMOTE_ADDR']));
            }
        }
        
        (new Logger())->info('Přístup na stránku index z IP adresy {ip}', array('ip' => $_SERVER['REMOTE_ADDR']));
        
        self::$pageHeader['title'] = 'Poznávačky';
        self::$pageHeader['description'] = 'Čeká vás poznávačka z biologie? Není lepší způsob, jak se na ni naučit, než použitím této webové aplikace. Vytvořte si vlastní poznávačku, společně do ní přidávejte obrázky, učte se z nich a nechte si generovat náhodné testy.';
        self::$pageHeader['keywords'] = 'poznávačky, biologie, příroda, poznávačka, přírodopis, přírodověda, test, výuka, naučit, učit, testy, učení';
        self::$pageHeader['cssFiles'] = array('css/index.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/ajaxMediator.js', 'js/index.js');
        self::$pageHeader['bodyId'] = 'index';
        self::$data['ee'] = (rand(0,99) === 73);
    }
}

