<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\DatabaseItems\Group;
use Poznavacky\Models\DatabaseItems\Part;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\ChangelogManager;
use Poznavacky\Models\Logger;
use \BadMethodCallException;

/**
 * Kontroler starající se o zobrazení layoutu pro všechny stránky kromě indexu
 * @author Jan Štěch
 */
class MenuController extends Controller
{
    private array $argumentsToPass = array();

    /**
     * Metoda rozhodující o tom, co se v layoutu zadaném v menu.phtml robrazí podle počtu specifikovaných argumentů v URL
     * @param array $parameters Parametry pro zpracování kontrolerem, zde seznam URL argumentů v postupném pořadí jako prvky pole
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        $this->data['navigationBar'] = array();
        $this->data['navigationBar'][] = array('text' => 'Menu', 'link' => 'menu');

        //Načtení argumentů vztahujících se k této stránce
        //Minimálně 0 (v případě domena.cz/menu)
        //Maximálně 5 (v případě domena.cz/menu/nazev-tridy/nazev-poznavacky/nazev-casti/akce/ajax-kontroller)
        $menuArguments = array();
        $parametersCopy = $parameters;
        for ($i = 0; $i < 5 && $arg = array_shift($parametersCopy); $i++)
        {
            $menuArguments[] = $arg;
        }

        $argumentCount = count($menuArguments);
        $aChecker = new AccessChecker();

        if ($argumentCount === 0)
        {
            //Vypisují se třídy

            //Vymazání objektů skladujících vybranou složku ze $_SESSION
            $this->unsetSelection(true, true, true);
        }
        if ($argumentCount > 0)
        {
            $controllerName = $this->kebabToCamelCase($menuArguments[0]).self::CONTROLLER_EXTENSION;
            $pathToController = $this->controllerExists($controllerName);
            if ($pathToController && $argumentCount === 1)
            {
                //AdministrateController nebo AccountSettingsController
                $this->controllerToCall = new $pathToController();

                //Vymazání objektů skladujících vybranou složku ze $_SESSION
                $this->unsetSelection(true, true, true);
                $this->argumentsToPass = array_slice($menuArguments, 1);
            }
            else
            {
                //Název třídy
                //Kontrola, zda právě zvolený název souhlasí s názvem třídy uložené v $_SESSION
                if (!isset($_SESSION['selection']['class']) || $menuArguments[0] !== $_SESSION['selection']['class']->getUrl())
                {
                    //Uložení objektu třídy do $_SESSION
                    $_SESSION['selection']['class'] = new ClassObject(false, 0);
                    $_SESSION['selection']['class']->initialize(null, $menuArguments[0]);
                    try
                    {
                        $_SESSION['selection']['class']->load();
                    }
                    catch (BadMethodCallException $e)
                    {
                        //Třída splňující daná kritéria neexistuje
                        (new Logger(true))->warning('Uživatel s ID {userId} přistupující do systému z IP adresy {ip} se pokusil vstoupit do třídy, která nebyla v databázi nalezena (URL reprezentace {classUrl})', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'classUrl' => $menuArguments[0]));
                        $this->redirect('error404');
                    }

                    //Vymazání objektů skladujících vybranou poznávačku a část ze $_SESSION
                    $this->unsetSelection(true, true);
                }

                //Kontrola, zda má uživatel do třídy přístup
                if (!($_SESSION['selection']['class']->checkAccess(UserManager::getId(), true) || $aChecker->checkSystemAdmin()))
                {
                    $closedClassId = $_SESSION['selection']['class']->getId();
                    $this->unsetSelection(true, true, true);    //Vymaž právě nastavenou třídu ze $_SESSION
                    (new Logger(true))->warning('Uživatel s ID {userId} přistupující do systému z IP adresy {ip} odeslal požadavek na zobrazení obsahu třídy s ID {classId}, do které ale nemá přístup', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'classId' => $closedClassId));

                    //Zkontroluj, zda je požadavek AJAX
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' )
                    {
                        //Požadavek je AJAX --> Navrať jenom chybový kód
                        header('HTTP/1.0 403 Forbidden');
                        exit();
                    }
                    else
                    {
                        //Požadavek není AJAX --> přesměruj na chybovou stránku
                        $this->redirect('error403');
                    }

                }

                $this->data['navigationBar'][] = array(
                    'text' => $_SESSION['selection']['class']->getName(),
                    'link' => 'menu/'.$_SESSION['selection']['class']->getUrl()
                );

                if ($argumentCount === 1)
                {
                    //Vymazání objektů skladujících vybranou poznávačku a část ze $_SESSION
                    $this->unsetSelection(true, true);
                }
            }
        }
        if ($argumentCount > 1 && !isset($this->controllerToCall))
        {
            $controllerName = $this->kebabToCamelCase($menuArguments[1]).self::CONTROLLER_EXTENSION;
            $pathToController = $this->controllerExists($controllerName);
            if ($pathToController && ($argumentCount === 2 || $controllerName === 'ManageController'))
            {
                //ManageController / LeaveController
                $this->controllerToCall = new $pathToController();
                $this->argumentsToPass = array_slice($menuArguments, 2);

                if ($controllerName === 'LeaveController')
                {
                    //Vymazání objektů skladujících vybranou poznávačku a část ze $_SESSION
                    //Toto nedělej při zvolení ManageController, protože v $_SESSION['selection'] mohou být uloženy pozměněné informace, které mají mít přednost před URL argumenty
                    $this->unsetSelection(true, true);
                }
            }
            else
            {
                //Název poznávačky
                //Kontrola, zda právě zvolený název souhlasí s názvem poznávačky uložené v $_SESSION
                if (!isset($_SESSION['selection']['group']) || $menuArguments[1] !== @$_SESSION['selection']['group']->getUrl())
                {
                    //Uložení objektu poznávačky do $_SESSION
                    $_SESSION['selection']['group'] = new Group(false);
                    $_SESSION['selection']['group']->initialize(null, $menuArguments[1], $_SESSION['selection']['class'], null, null);
                    try
                    {
                        $_SESSION['selection']['group']->load();
                    }
                    catch (BadMethodCallException $e)
                    {
                        //Poznávačka splňující daná kritéria neexistuje
                        (new Logger(true))->warning('Uživatel s ID {userId} přistupující do systému z IP adresy {ip} se pokusil zobrazit obsah poznávačky, která nebyla v databázi nalezena (URL reprezentace {groupUrl})', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'groupUrl' => $menuArguments[1]));
                        $this->redirect('error404');
                    }
                    //Vymazání objektů skladujících vybranou část ze $_SESSION
                    $this->unsetSelection(true);
                }

                $this->data['navigationBar'][] = array(
                    'text' => $_SESSION['selection']['group']->getName(),
                    'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl()
                );

                if ($argumentCount === 2)
                {
                    //Vymazání objektů skladujících vybranou část ze $_SESSION
                    $this->unsetSelection(true);
                }
            }
        }
        if ($argumentCount > 2 && !isset($this->controllerToCall))
        {
            //Jsou zvoleny všechny části najednou?
            $controllerName = $this->kebabToCamelCase($menuArguments[2]).self::CONTROLLER_EXTENSION;
            $pathToController = $this->controllerExists($controllerName);
            if ($pathToController)
            {
                //Ano
                $this->controllerToCall = new $pathToController();
                $this->argumentsToPass = array_slice($menuArguments, 3);

                //Vymazání objektu skladujícího vybranou část ze $_SESSION
                $this->unsetSelection(true);
            }
            else
            {
                //Ne --> v dalším argumentu musí být specifikována akce
                if ($argumentCount === 3)
                {
                    //Je specifikována část, ale ne akce --> návrat na seznam částí
                    (new Logger(true))->warning('Uživatel s ID {userId} přistupující do systému z IP adresy {ip} zvolil platnou třídu, poznávačku i část, avšak nespecifikoval žádnou akci', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
                    $this->redirect('menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl());
                }

                //Nastavení části (pouze, pokud nejsou vybrány všechny části najednou)
                //Kontrola, zda právě zvolený název souhlasí s názvem třídy uložené v $_SESSION
                if (!isset($_SESSION['selection']['part']) || $menuArguments[2] !== @$_SESSION['selection']['part']->getUrl())
                {
                    //Uložení objektu části do $_SESSION
                    $_SESSION['selection']['part'] = new Part(false);
                    $_SESSION['selection']['part']->initialize(null, $menuArguments[2], $_SESSION['selection']['group'], null, null, null);
                    try
                    {
                        $_SESSION['selection']['part']->load();
                    }
                    catch (BadMethodCallException $e)
                    {
                        //Část splňující daná kritéria neexistuje
                        (new Logger(true))->warning('Uživatel s ID {userId} přistupující do systému z IP adresy {ip} se pokusil vstoupit do části, která nebyla v databázi nalezena (URL reprezentace {partUrl})', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'partUrl' => $menuArguments[2]));
                        $this->redirect('error404');
                    }
                }

                $this->data['navigationBar'][] = array(
                    'text' => $_SESSION['selection']['part']->getName(),
                    'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl().'/'.$_SESSION['selection']['part']->getUrl()
                );
            }
        }
        if ($argumentCount > 3 && !isset($this->controllerToCall))
        {
            //Akce pro část
            $controllerName = $this->kebabToCamelCase($menuArguments[3]).self::CONTROLLER_EXTENSION;
            $pathToController = $this->controllerExists($controllerName);
            if ($pathToController)
            {
                $this->controllerToCall = new $pathToController();
                $this->argumentsToPass = array_slice($menuArguments, 4);
            }
            else
            {
                //Neplatný kontroler
                (new Logger(true))->warning('Uživatel s ID {userId} přistupující do systému z IP adresy {ip} zvolil třídu, poznávačku i část, avšak kontroler pro specifikovanou akci ({action}) nebyl nalezen', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'action' => $menuArguments[3]));
                $this->redirect('error404');
            }
        }

        if (isset($this->controllerToCall))
        {
            //Kontroler je nastaven --> předat posbírané argumenty dál
            $this->controllerToCall->process($this->argumentsToPass);
            $this->pageHeader['bodyId'] = $this->controllerToCall->pageHeader['bodyId'];
            $this->data['navigationBar'] = array_merge($this->data['navigationBar'], $this->controllerToCall->data['navigationBar']);
        }
        else
        {
            //Kontroler není nastaven --> vypsat tabulku na menu stránce
            $this->pageHeader['bodyId'] = 'menu';
            $controllerName = __NAMESPACE__.'\\MenuTable'.self::CONTROLLER_EXTENSION;
            $this->controllerToCall = new $controllerName();
            $this->controllerToCall->process(array(true)); //Pole nesmí být prázdné, aby si systém nemyslel, že uživatel přistupuje ke kontroleru přímo

            //Aktualizovat poslední navštívenou tabulku na menu stránce
            UserManager::getUser()->updateLastMenuTableUrl(implode('/', $parameters));
        }

        $this->pageHeader['title'] = $this->controllerToCall->pageHeader['title'];
        $this->pageHeader['description'] = $this->controllerToCall->pageHeader['description'];
        $this->pageHeader['keywords'] = $this->controllerToCall->pageHeader['keywords'];
        $this->pageHeader['cssFiles'] = $this->controllerToCall->pageHeader['cssFiles'];
        $this->pageHeader['jsFiles'] = $this->controllerToCall->pageHeader['jsFiles'];

        $this->data['loggedUserName'] = UserManager::getName();
        $this->data['adminLogged'] = $aChecker->checkSystemAdmin();
        $this->data['demoVersion'] = $aChecker->checkDemoAccount();

        $changelogManager = new ChangelogManager();
        if (!$changelogManager->checkLatestChangelogRead())
        {
            UserManager::getUser()->updateLastSeenChangelog(ChangelogManager::LATEST_VERSION);
            $this->data['staticTitle'] = array($changelogManager->getTitle());
            $this->data['staticContent'] = array($changelogManager->getContent());
            (new Logger(true))->info('Uživateli s ID {userId} byl zobrazen nejnovější changelog pro verzi {version}', array('userId' => UserManager::getId(), 'version' => ChangelogManager::LATEST_VERSION));
        }

        $this->view = 'menu';
    }

    /**
     * Metoda odstraňující ze $_SESSION objekty ukládající vybranou třídu, poznávačku, nebo její část
     * @param bool $unsetPart TRUE, pokud se má odstranit část; defaultně FALSE
     * @param bool $unsetGroup TRUE, pokud se má odstranit poznávačka; defaultně FALSE
     * @param bool $unsetClass TRUE, pokud se má odstranit třída; defaultně FALSE
     */
    private function unsetSelection(bool $unsetPart = false, bool $unsetGroup = false, bool $unsetClass = false): void
    {
        if ($unsetPart){ unset($_SESSION['selection']['part']); }
        if ($unsetGroup){ unset($_SESSION['selection']['group']); }
        if ($unsetClass){ unset($_SESSION['selection']['class']); }
    }
}

