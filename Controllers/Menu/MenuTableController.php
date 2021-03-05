<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Exceptions\NoDataException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\TestGroupsFetcher;
use Poznavacky\Models\Logger;

/** 
 * Kontroler starající se o rozhodnutí, jaká tabulka se bude zobrazovat na menu stránce
 * Tato třída nastavuje pohled obsahující poze tlačítko pro návrat a/nebo chybovou hlášku
 * Pohled obsahující samotnou tabulku a její obsah je nastavován kontrolerem MenuTableContentController
 * K tomuto kontroleru nelze přistupovat přímo (z URL adresy)
 * @author Jan Štěch
 */
class MenuTableController extends SynchronousController
{

    /**
     * Metoda nastavující informace pro hlavičku stránky a získávající data do tabulky
     * @param array $parameters Pole parametrů, pokud je prázdné, je přístup ke kontroleru zamítnut
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        if (empty($parameters))
        {
            //Uživatel se pokouší k tomuto kontroleru přistoupit přímo
            (new Logger(true))->warning('Uživatel z IP adresy {ip} se pokusil manuálně přistoupit přímo ke kontroleru pro získání obsahu třídy nebo poznávačky', array('ip' => $_SERVER['REMOTE_ADDR']));
            $this->redirect('menu');
        }

        self::$pageHeader['title'] = 'Volba poznávačky';
        self::$pageHeader['description'] = 'Zvolte si poznávačku, na kterou se chcete učit.';
        self::$pageHeader['keywords'] = 'poznávačky, biologie, příroda';
        self::$pageHeader['cssFiles'] = array('css/css.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js','js/menu.js', 'js/folders.js', 'js/invitations.js');
        self::$pageHeader['bodyId'] = 'menu';
        
        //Získání dat
        $dataForTable = null;
        $viewForTable = null;
        $aChecker = new AccessChecker();
        try
        {
            if (!$aChecker->checkClass())
            {
                $classesGetter = new TestGroupsFetcher();
                $classes = $classesGetter->getClasses();
                (new Logger(true))->info('K uživateli s ID {userId} přistupujícímu do systému z IP adresy {ip} byl odeslán seznam dostupných tříd', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
                $this->controllerToCall = new MenuTableContentController();
                $dataForController = $classes;
            }
            else if (!$aChecker->checkGroup())
            {
                $groupsGetter = new TestGroupsFetcher();
                $groups = $groupsGetter->getGroups($_SESSION['selection']['class']);
                (new Logger(true))->info('K uživateli s ID {userId} přistupujícímu do systému z IP adresy {ip} byl odeslán seznam poznávaček ve třídě s ID {classId}', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'classId' => $_SESSION['selection']['class']->getId()));
                $this->controllerToCall = new MenuTableContentController();
                $dataForController = $groups;
            }
            else
            {
                $partsGetter = new TestGroupsFetcher();
                $parts = $partsGetter->getParts($_SESSION['selection']['group']);
                (new Logger(true))->info('K uživateli s ID {userId} přistupujícímu do systému z IP adresy {ip} byl odeslán seznam částí v poznávačce s ID {groupId} ve třídě s ID {classId}', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'groupId' => $_SESSION['selection']['group']->getId(), 'classId' => $_SESSION['selection']['class']->getId()));
                $this->controllerToCall = new MenuTableContentController();
                $dataForController = $parts;
            }
        }
        catch (NoDataException $e)
        {
            (new Logger(true))->notice('Uživatel s ID {userId} přistupující do systému z IP adresy {ip} odeslal požadavek na zobrazení obsahu třídy, poznávačky nebo části, která žádný obsah nemá', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            $this->controllerToCall = new MenuTableContentController();
            $dataForController = $e->getMessage();
        }
        
        //Obsah pro tabulku a potřebný pohled je v potomkovém kontroleru nastaven --> vypsat data
        $this->controllerToCall->process($dataForController); //Pole nesmí být prázdné, aby si systém nemyslel, že uživatel přistupuje ke kontroleru přímo
    }
}

