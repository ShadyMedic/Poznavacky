<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\Logger;

/**
 * Kontroler starající se o vypsání úvodní stránky webu
 * @author Jan Štěch
 */
class HoneypotController extends SynchronousController
{
    
    /**
     * Metoda nastavující hlavičku stránky a pohled k zobrazení
     * @param array $parameters Parametry pro kontroler (nevyužíváno)
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        file_put_contents('.htaccess', 'Deny from '.$_SERVER['REMOTE_ADDR'].PHP_EOL, FILE_APPEND | LOCK_EX); //Toto fakt není ideální, přímé upravování .htaccess je asi ta největší zrůdnost, co jsem kdy naprogramoval, ale prozartím to bude fungovat. Ideálně by se ten blacklist měl sestavovat v nějakém odděleném textovém souboru a .htaccess by jej měl načítat.
        (new Logger())->info('Nepřihlášený uživatel přistupující na server z IP adresy {ip} byl přidán na blacklist IP adres kvůli pokusu přisoupit k (neexistujícímu) souboru .env.',
                array('ip' => $_SERVER['REMOTE_ADDR']));
        exit('You have been permanently blocked for URL-probing.'); //Tohle také není úplně elegantní. Ale hej, tohle je jenom pro roboty a hackery...
    }
}

