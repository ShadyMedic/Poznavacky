<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Exceptions\NoDataException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Security\NumberAsWordCaptcha;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\ChangelogManager;
use Poznavacky\Models\Logger;
use Poznavacky\Models\TestGroupsFetcher;

/**
 * Kontroler starající se o zobrazení tabulky tříd (a pozvánek do nich), poznávaček, nebo částí
 * Dále se stará o zobrazování changelogu
 * @author Jan Štěch
 */
class MenuController extends SynchronousController
{

    /**
     * Metoda nastavující hlavičky stránky a získává informace pro zobrazení v tabulce
     * Dále také zjišťuje, zda má být uživateli zobrazen changelog a případně jej získává
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @throws DatabaseException
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        self::$pageHeader['title'] = 'Volba poznávačky';
        self::$pageHeader['description'] = 'Zvolte si poznávačku, na kterou se chcete učit.';
        self::$pageHeader['keywords'] = 'poznávačky, biologie, příroda';
        self::$pageHeader['cssFiles'] = array('css/css.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js','js/ajaxMediator.js','js/menu.js','js/folders.js', 'js/invitations.js');
        self::$pageHeader['bodyId'] = 'menu';

        //Získání dat pro tabulku
        $dataForTable = null;
        $viewForTable = null;
        $aChecker = new AccessChecker();
        try
        {
            if (!$aChecker->checkClass())
            {
                $classesGetter = new TestGroupsFetcher();
                $classes = $classesGetter->getClasses();
                $lastVisitedFolderPath = '';
                self::$data['table'] = $classes;
                self::$data['invitations'] = UserManager::getUser()->getActiveInvitations();
                (new Logger(true))->info('K uživateli s ID {userId} přistupujícímu do systému z IP adresy {ip} byl odeslán seznam dostupných tříd', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            }
            else if (!$aChecker->checkGroup())
            {
                $groupsGetter = new TestGroupsFetcher();
                $groups = $groupsGetter->getGroups($_SESSION['selection']['class']);
                $lastVisitedFolderPath = $_SESSION['selection']['class']->getUrl();
                (new Logger(true))->info('K uživateli s ID {userId} přistupujícímu do systému z IP adresy {ip} byl odeslán seznam poznávaček ve třídě s ID {classId}', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'classId' => $_SESSION['selection']['class']->getId()));
                self::$data['table'] = $groups;
            }
            else
            {
                $partsGetter = new TestGroupsFetcher();
                $parts = $partsGetter->getParts($_SESSION['selection']['group']);
                $lastVisitedFolderPath = $_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl();
                (new Logger(true))->info('K uživateli s ID {userId} přistupujícímu do systému z IP adresy {ip} byl odeslán seznam částí v poznávačce s ID {groupId} ve třídě s ID {classId}', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'groupId' => $_SESSION['selection']['group']->getId(), 'classId' => $_SESSION['selection']['class']->getId()));
                self::$data['table'] = $parts;
            }

            //Aktualizovat poslední navštívenou tabulku na menu stránce
            UserManager::getUser()->updateLastMenuTableUrl($lastVisitedFolderPath);
        }
        catch (NoDataException $e)
        {
            (new Logger(true))->notice('Uživatel s ID {userId} přistupující do systému z IP adresy {ip} odeslal požadavek na zobrazení obsahu třídy, poznávačky nebo části, která žádný obsah nemá', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));

            //Nahraď pohled s tabulkou pohledem pro obyčejnou hlášku
            for ($i = 0; $i < count(self::$views); $i++)
            {
                $view = self::$views[$i];
                if (
                    $view === 'menuClassesTable' ||
                    $view === 'menuGroupsTable' ||
                    $view === 'menuPartsTable'
                ) { self::$views[$i] = 'menuTableMessage'; }
            }

            self::$data['message'] = $e->getMessage();
        }

        $changelogManager = new ChangelogManager();
        if (!$changelogManager->checkLatestChangelogRead())
        {
            UserManager::getUser()->updateLastSeenChangelog(ChangelogManager::LATEST_VERSION);
            self::$data['staticTitle'] = array($changelogManager->getTitle());
            self::$data['staticContent'] = array($changelogManager->getContent());
            self::$data['displayOverlay'] = true;
            (new Logger(true))->info('Uživateli s ID {userId} byl zobrazen nejnovější changelog pro verzi {version}', array('userId' => UserManager::getId(), 'version' => ChangelogManager::LATEST_VERSION));
        }

        //Data pro formulář pro založení nové třídy
        $antispamGenerator = new NumberAsWordCaptcha();
        $antispamGenerator->generate();
        self::$data['antispamCode'] = $antispamGenerator->question;
        self::$data['specifiedEmail'] = (empty(UserManager::getEmail())) ? false : true;
    }
}

