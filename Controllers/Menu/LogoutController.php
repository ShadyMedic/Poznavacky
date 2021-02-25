<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\Processors\LoginUser;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\MessageBox;

/**
 * Kontroler starající se o odhlášení uživatele a jeho přesměrování na index stránku.
 * @author Jan Štěch
 */
class LogoutController extends Controller
{

    /**
     * Metoda odhlašující uživatele a přesměrovávající jej na index stránku
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        //Vymaž současné odhlášení uživatele
        unset($_SESSION['user']);
        
        //Odstraň trvalé přihlášení
        if (isset($_COOKIE['instantLogin']))
        {
            $code = $_COOKIE['instantLogin'];
            
            //Odstraň cookie pro trvalé přihlášení
            unset($_COOKIE['instantLogin']);
            setcookie('instantLogin', null, -1);
            
            //Vymaž kód pro trvalé přihlášení z databáze
            Db::executeQuery('DELETE FROM sezeni WHERE kod_cookie = ? LIMIT 1', array(md5($code)));
            unset($code);
        }
        
        $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Byli jste úspěšně odhlášeni');
        
        //Přesměrování na index
        $this->redirect('');
    }
}

