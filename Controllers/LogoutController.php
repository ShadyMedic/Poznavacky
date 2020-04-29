<?php
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
    public function process(array $paremeters)
    {
        //Vymaž současné odhlášení uživatele
        session_start();
        unset($_SESSION['user']);
        
        //Odstraň trvalé přihlášení
        if (isset($_COOKIE['instantLogin']))
        {
            $code = $_COOKIE['instantLogin'];
            
            //Odstraň cookie pro trvalé přihlášení
            unset($_COOKIE['instantLogin']);
            setcookie('instantLogin', null, -1);
            
            //Vymaž kód pro trvalé přihlášení z databáze
            Db::connect();
            Db::executeQuery('DELETE FROM sezeni WHERE kod_cookie = ? LIMIT 1', array(md5($code)));
            unset($code);
        }
        
        //Přesměrování na index
        $this->redirect('');
    }
}

