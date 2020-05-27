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
        $class = new ClassObject(0, $parameters['class']);
        $group = new Group(0, $parameters['group'], $class);
        if (isset($parameters['part']))
        {
            $part = new Part(0, $parameters['part'], $group);
            $allParts = false;
        }
        else
        {
            $allParts = true;
        }
        
        //Kontrola přístupu
        if (!$class->checkAccess(UserManager::getId()))
        {
            $this->redirect('error403');
        }
        
        $this->data['previousNatural'] = '';
        $this->data['previousUrl'] = '';
        
        //Kontrola odeslání formuláře
        if (!empty($_POST))
        {
            $adder = new PictureAdder($class, $group);
            try
            {
                if ($adder->processFormData($_POST))
                {
                    $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Obrázek úspěšně přidán');
                    
                    //Vymaž data z $_POST
                    if ($allParts)
                    {
                        $this->redirect('menu/'.$class->getName().'/'.$group->getName().'/add-pictures');
                    }
                    else
                    {
                        $this->redirect('menu/'.$class->getName().'/'.$group->getName().'/'.$part->getName().'/add-pictures');
                    }
                }
            }
            catch (AccessDeniedException $e)
            {
                $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, $e->getMessage());
                
                //Obnov data
                $this->data['previousNatural'] = $_POST['naturalName'];
                $this->data['previousUrl'] = $_POST['url'];
            }
        }
        
        $this->pageHeader['title'] = 'Přidat obrázky';
        $this->pageHeader['description'] = 'Přidávejte obrázky do své poznávačky, aby se z nich mohli učit všichni členové třídy';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFile'] = 'css/css.css';
        $this->pageHeader['jsFile'] = 'js/addPictures.js';
        $this->pageHeader['bodyId'] = 'addPictures';
        
        if ($allParts)
        {
            $this->data['naturals'] = $group->getNaturals();
        }
        else
        {
            $this->data['naturals'] = $part->getNaturals();
        }
        
        $this->data['returnUrl'] = 'menu/'.$class->getName().'/'.$group->getName();
        
        $this->view = 'addPictures';
    }
}