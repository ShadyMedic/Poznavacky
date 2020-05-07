<?php
/** 
 * Kontroler starající se o rozhodnutí, jaká tabulka se bude zobrazovat na menu stránce
 * Tato třída nastavuje pohled obsahující poze tlačítko pro návrat a/nebo chybovou hlášku
 * Pohled obsahující samotnou tabulku a její obsah je nastavován kontrolerem MenuTableContentController
 * @author Jan Štěch
 */
class MenuTableController extends Controller
{

    /**
     * Metoda nastavující informace pro hlavičku stránky a získávající data do tabulky
     * @see Controller::process()
     */
    public function process(array $chosenFolder)
    {
        $this->pageHeader['title'] = 'Volba poznávačky';
        $this->pageHeader['description'] = 'Zvolte si poznávačku, na kterou se chcete učit.';
        $this->pageHeader['keywords'] = 'poznávačky, biologie, příroda';
        $this->pageHeader['cssFile'] = 'css/css.css';
        $this->pageHeader['jsFile'] = 'js/menu.js';
        $this->pageHeader['bodyId'] = 'menu';
        
        //Doplnění argumentů práznými řetězci
        for ($i = 0; $i < 3; $i++)
        {
            if (!isset($chosenFolder[$i])){$chosenFolder[$i] = '';}
        }
        
        $className = $chosenFolder[0];
        $groupName = $chosenFolder[1];
        
        //Získání dat
        try
        {
            if (empty($className))
            {
                $this->view = 'menuClassesForms';
                $classes = TestGroupsManager::getClasses();
                $this->controllerToCall = new MenuTableContentController('menuClassesTable', $classes);
            }
            else if (empty($groupName))
            {
                $this->data['returnButtonLink'] = 'menu';
                $this->view = 'menuGroupsButton';
                $groups = TestGroupsManager::getGroups($className);
                $this->controllerToCall = new MenuTableContentController('menuGroupsTable', $groups);
            }
            else
            {
                $this->data['returnButtonLink'] = 'menu/'.$className;
                $this->view = 'menuPartsButton';
                $parts = TestGroupsManager::getParts($className, $groupName);
                $this->controllerToCall = new MenuTableContentController('menuPartsTable', $parts);
            }
        }
        catch (AccessDeniedException $e)
        {
            if ($e->getMessage() === AccessDeniedException::REASON_USER_NOT_LOGGED_IN)
            {
                //Uživatel není přihlášen
                $this->redirect('error403');
                return;
            }
            else
            {
                //Uživatel nemá přístup do třídy nebo poznávačky
                $this->controllerToCall = new MenuTableContentController('menuTableMessage', $e->getMessage());
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
        $this->controllerToCall->process(array());
    }
}