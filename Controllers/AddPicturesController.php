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
        $class = new ClassObject(0, $parameters[0]);
        if (!$class->checkAccess(UserManager::getId()))
        {
            $this->redirect('error403');
        }
        
        $class = new ClassObject(0, $parameters[0]);
        $group = new Group(0, $parameters[1], $class);
        if (isset($parameters[2]))
        {
            $part = new Part(0, $parameters[2], $group);
            $allParts = false;
        }
        else
        {
            $allParts = true;
        }
        
        if ($allParts){ $this->data['naturals'] = $group->getNaturals(); }
        else { $this->data['naturals'] = $part->getNaturals(); }
        
        $this->data['returnUrl'] = 'menu/'.$class->getName().'/'.$group->getName();
        
        $this->pageHeader['title'] = 'Přidat obrázky';
        $this->pageHeader['description'] = 'Přidávejte obrázky do své poznávačky, aby se z nich mohli učit všichni členové třídy';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFile'] = 'css/css.css';
        $this->pageHeader['jsFile'] = 'js/addPictures.js';
        $this->pageHeader['bodyId'] = 'addPictures';
        
        $this->view = 'addPictures';
    }
}