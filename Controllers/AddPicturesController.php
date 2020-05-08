<?php
/** 
 * Kontroler starající se o výpis stránky pro přidání obrázků
 * @author Jan Štěch
 */
class AddPicturesController extends Controller
{

    /**
     * Metoda ověřující, zda má uživatel do třídy přístup a nastavující hlavičku stránky a pohled
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        if (!AccessChecker::checkAccess(UserManager::getId(), ClassManager::getIdByName($parameters[0])))
        {
            $this->redirect('error403');
        }
        
        $this->pageHeader['title'] = 'Přidat obrázky';
        $this->pageHeader['description'] = 'Přidávejte obrázky do své poznávačky, aby se z nich mohli učit všichni členové třídy';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFile'] = 'css/css.css';
        $this->pageHeader['jsFile'] = 'js/addPictures.js';
        $this->pageHeader['bodyId'] = 'addPictures';
        
        $this->view = 'addPictures';
    }
}