<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Security\AntiCsrfMiddleware;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use Poznavacky\Models\MessageBox;

/**
 * Kontroler starající se o odhlášení uživatele a jeho přesměrování na index stránku.
 * @author Jan Štěch
 */
class LogoutController extends SynchronousController
{

    /**
     * Metoda odhlašující uživatele a přesměrovávající jej na index stránku
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws DatabaseException Pokud se nepodaří odstranit kód pro trvalé přihlhášení  (za předpokladu, že je přítomen)
     * @throws AccessDeniedException Pokud není přilhášen žádný uživatel
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        $userId = UserManager::getId();
        
        //Odstraň trvalé přihlášení
        if (isset($_COOKIE['instantLogin']))
        {
            $code = $_COOKIE['instantLogin'];
            
            //Odstraň cookie pro trvalé přihlášení
            unset($_COOKIE['instantLogin']);
            setcookie('instantLogin', null, -1, '/');
            
            //Vymaž kód pro trvalé přihlášení z databáze
            Db::executeQuery('DELETE FROM sezeni WHERE kod_cookie = ? LIMIT 1', array(md5($code)));
            unset($code);
        }

        //Vymaž současné přihlášení uživatele a s ním i všechno ostatní v $_SESSION
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(), '', 0, '/');
        session_regenerate_id();
        
        $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Byli jste úspěšně odhlášeni');
        (new Logger(true))->info('Uživatel s ID {userId} se z IP adresy {ip} odhlásil', array('userId' => $userId, 'ip' => $_SERVER['REMOTE_ADDR']));

        //Přesměrování na index
        $this->redirect('');
    }
}

