<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\ChangelogManager;
use Poznavacky\Models\Logger;

/**
 * Kontroler starající se o zobrazení layoutu pro všechny stránky kromě indexu
 * @author Jan Štěch
 */
class MenuController extends SynchronousController
{

    /**
     * Metoda rozhodující o tom, co se v layoutu zadaném v menu.phtml robrazí podle počtu specifikovaných argumentů v URL
     * @param array $parameters Parametry pro zpracování kontrolerem, zde seznam URL argumentů v postupném pořadí jako prvky pole
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @throws DatabaseException
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        //Vypsání tabulky na menu stránce
        self::$pageHeader['bodyId'] = 'menu';
        $controllerName = __NAMESPACE__.'\\MenuTable'.self::CONTROLLER_EXTENSION;
        $controllerToCall = new $controllerName();
        $controllerToCall->process(array(true)); //Pole nesmí být prázdné, aby si systém nemyslel, že uživatel přistupuje ke kontroleru přímo

        //Aktualizovat poslední navštívenou tabulku na menu stránce
        UserManager::getUser()->updateLastMenuTableUrl(implode('/', $parameters));

        $changelogManager = new ChangelogManager();
        if (!$changelogManager->checkLatestChangelogRead())
        {
            UserManager::getUser()->updateLastSeenChangelog(ChangelogManager::LATEST_VERSION);
            self::$data['staticTitle'] = array($changelogManager->getTitle());
            self::$data['staticContent'] = array($changelogManager->getContent());
            (new Logger(true))->info('Uživateli s ID {userId} byl zobrazen nejnovější changelog pro verzi {version}', array('userId' => UserManager::getId(), 'version' => ChangelogManager::LATEST_VERSION));
        }
    }
}

