<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\Exceptions\NoDataException;
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
class MenuTableController extends Controller
{

    /**
     * Metoda nastavující informace pro hlavičku stránky a získávající data do tabulky
     * @param array $parameters Pole parametrů, pokud je prázdné, je přístup ke kontroleru zamítnut
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        if (empty($parameters))
        {
            //Uživatel se pokouší k tomuto kontroleru přistoupit přímo
            $this->redirect('menu');
        }

        $this->pageHeader['title'] = 'Volba poznávačky';
        $this->pageHeader['description'] = 'Zvolte si poznávačku, na kterou se chcete učit.';
        $this->pageHeader['keywords'] = 'poznávačky, biologie, příroda';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/menu.js', 'js/folders.js', 'js/invitations.js');
        $this->pageHeader['bodyId'] = 'menu';
        
        //Získání dat
        try
        {
            if (!isset($_SESSION['selection']['class']))
            {
                $classesGetter = new TestGroupsFetcher();
                $classes = $classesGetter->getClasses();
                $this->controllerToCall = new MenuTableContentController('menuClassesTable', $classes);
            }
            else if (!isset($_SESSION['selection']['group']))
            {
                $groupsGetter = new TestGroupsFetcher();
                $groups = $groupsGetter->getGroups($_SESSION['selection']['class']);
                $this->controllerToCall = new MenuTableContentController('menuGroupsTable', $groups);
            }
            else
            {
                $partsGetter = new TestGroupsFetcher();
                $parts = $partsGetter->getParts($_SESSION['selection']['group']);
                $this->controllerToCall = new MenuTableContentController('menuPartsTable', $parts);
            }
        }
        catch (NoDataException $e)
        {
            if ($e->getMessage() === NoDataException::UNKNOWN_CLASS || $e->getMessage() === NoDataException::UNKNOWN_GROUP || $e->getMessage() === NoDataException::UNKNOWN_PART)
            {
                $this->redirect('error404');
            }
            $this->controllerToCall = new MenuTableContentController('menuTableMessage', $e->getMessage());
        }
        
        //Obsah pro tabulku a potřebný pohled je v potomkovém kontroleru nastaven --> vypsat data
        $this->controllerToCall->process(array(true)); //Pole nesmí být prázdné, aby si systém nemyslel, že uživatel přistupuje ke kontroleru přímo
        $this->view = 'inherit';
    }
}

