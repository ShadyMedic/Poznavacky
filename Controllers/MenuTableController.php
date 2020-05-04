<?php

/** 
 * Kontroler starající se o výpis tabulky tříd, poznávaček nebo částí na menu stránce
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
        
        //Získání dat
        $this->getData($chosenFolder[0], $chosenFolder[1]);
        
        $this->view = 'menuTable';
    }
    
    /**
     * Metoda získávající data pro tabulku na menu stránku
     * @param string $className Jméno třídy (pokud byla zvolena)
     * @param string $groupName Jméno poznávačky (pokud byla zvolena)
     */
    private function getData(string $className = '', string $groupName = '')
    {
        try
        {
            if (empty($className))
            {
                $classes = TestGroupsManager::getClasses();
                $this->data['tableColumns'] = 2;
                $this->data['tableData'] = $classes;
                $this->data['tableLevel'] = 0;
            }
            else if (empty($groupName))
            {
                $groups = TestGroupsManager::getGroups($className);
                $this->data['tableColumns'] = 2;
                $this->data['tableData'] = $groups;
                $this->data['tableLevel'] = 1;
            }
            else
            {
                $parts = TestGroupsManager::getParts($className, $groupName);
                $this->data['tableColumns'] = 3;
                $this->data['tableData'] = $parts;
                $this->data['tableLevel'] = 2;
            }
        }
        catch (AccessDeniedException $e)
        {
            if ($e->getMessage() === AccessDeniedException::REASON_USER_NOT_LOGGED_IN)
            {
                //Uživatel není přihlášen
                $this->redirect('Error403');
                return;
            }
            else
            {
                //Uživatel nemá přístup do třídy nebo poznávačky
                $this->data['tableColumns'] = 1;
                $this->data['tableData'] = array(0 => array(0 => $e->getMessage()));
                $this->data['tableLevel'] = $e->getAdditionalInfo('menuTableLevel');
            }
        }
        catch (NoDataException $e)
        {
            $this->data['tableColumns'] = 1;
            $this->data['tableData'] = array(0 => array(0 => $e->getMessage()));
            $this->data['tableLevel'] = $e->getTableLevel();
        }
    }
}