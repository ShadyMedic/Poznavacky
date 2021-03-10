<?php
namespace Poznavacky\Controllers\Menu;

use PHPMailer\PHPMailer\Exception;
use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Exceptions\NoDataException;
use Poznavacky\Models\Processors\NewClassRequester;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Security\NumberAsWordCaptcha;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\ChangelogManager;
use Poznavacky\Models\Logger;
use Poznavacky\Models\MessageBox;
use Poznavacky\Models\TestGroupsFetcher;

/**
 * Kontroler starající se o zobrazení tabulky tříd (a pozvánek do nich), poznávaček, nebo částí
 * Dále se stará o zobrazování changelogu a obsluhuje formulář pro založení nové třídy
 * @author Jan Štěch
 */
class MenuController extends SynchronousController
{

    /**
     * Metoda nastavující hlavičky stránky a získává informace pro zobrazení v tabulce
     * Dále také zjišťuje, zda má být uživateli zobrazen changelog a případně jej získává
     * Nakonec také obsluhuje formulář pro odeslání žádosti na založení nové třídy
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
        self::$pageHeader['jsFiles'] = array('js/generic.js','js/menu.js', 'js/folders.js', 'js/invitations.js');
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
            (new Logger(true))->info('Uživateli s ID {userId} byl zobrazen nejnovější changelog pro verzi {version}', array('userId' => UserManager::getId(), 'version' => ChangelogManager::LATEST_VERSION));
        }

        //Obsluha formuláře pro založení nové třídy
        if (!empty($_POST)) //Kontrola, zda právě nebyl formulář odeslán
        {
            //Kontrola, zda se nejedná o demo účet
            $aChecker = new AccessChecker();
            if ($aChecker->checkDemoAccount())
            {
                (new Logger(true))->warning('Uživatel používající demo účet z IP adresy {ip} odeslal formulář pro založení nové třídy, který byl ignorován', array('ip' => $_SERVER['REMOTE_ADDR']));
                $this->redirect('error403');
            }

            $requester = new NewClassRequester();
            try
            {
                if ($requester->processFormData($_POST))
                {
                    (new Logger(true))->info('Uživatel s ID {userId} odeslal z IP adresy {ip} žádost o založení nové třídy s názevem {className}', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'className' => $_POST['className']));
                    $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Žádost o založení nové třídy byla úspěšně odeslána. Sledujte prosím pravidelně svou e-mailovou schránku a očekávejte naši odpověď.');
                    $this->redirect('menu');
                }
                else
                {
                    throw new Exception();
                }
            }
            catch (AccessDeniedException $e)
            {
                $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, $e->getMessage());
            }
            catch (Exception $e)
            {
                //E-mail se nepodařilo odeslat
                (new Logger(true))->critical('Uživatel s ID {userId} přistupující do systému z IP adresy {ip} odeslal žádost o založení nové třídy se všemi náležitostmi, avšak e-mail se žádostí se webmasterovi se nepodařilo z neznámého důvodu odeslat; je možné že není možné odesílat žádné e-maily', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
                $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'E-mail se nepodařilo odeslat. Zkuste to prosím později, nebo pošlete svou žádost jako issue na GitHub (viz odkaz "Nalezli jste problém" v patičce stránky)');
            }

            //Obnov data
            self::$data['emailValue'] = @$_POST['email'];
            self::$data['classNameValue'] = @$_POST['className'];
            self::$data['textValue'] = @$_POST['text'];

            self::$data['displayNewClassForm'] = true;
        }
        else { self::$data['displayNewClassForm'] = false; }

        $antispamGenerator = new NumberAsWordCaptcha();
        $antispamGenerator->generate();
        self::$data['antispamCode'] = $antispamGenerator->question;
        self::$data['specifiedEmail'] = (empty(UserManager::getEmail())) ? false : true;
    }
}

