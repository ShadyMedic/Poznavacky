<?php
namespace Poznavacky\Controllers\Menu\Study\AddPictures;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Processors\PictureAdder;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\MessageBox;

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
    public function process(array $parameters): void
    {   
        $class = $_SESSION['selection']['class'];
        $group = $_SESSION['selection']['group'];
        if (isset($_SESSION['selection']['part']))
        {
            $part = $_SESSION['selection']['part'];
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

        //Kontrola přítomnosti přírodnin
        if (
            $allParts && count($group->getNaturals()) === 0 ||
            !$allParts && count($part->getNaturalsCount() === 0)
        )
        {
            //Žádné přírodniny
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, "V této části nebo poznávačce nejsou zatím přidané žádné přírodniny");
            $this->redirect('menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl());
        }

        $this->data['previousNatural'] = '';
        $this->data['previousUrl'] = '';
        
        //Kontrola odeslání formuláře
        if (!empty($_POST))
        {
            $adder = new PictureAdder($group);
            try
            {
                if ($adder->processFormData($_POST))
                {
                    $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Obrázek úspěšně přidán');
                    
                    //Vymaž data z $_POST
                    if ($allParts)
                    {
                        $this->redirect('menu/'.$class->getUrl().'/'.$group->getUrl().'/add-pictures');
                    }
                    else
                    {
                        $this->redirect('menu/'.$class->getUrl().'/'.$group->getUrl().'/'.$part->getUrl().'/add-pictures');
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
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/addPictures.js', 'js/menu.js');
        $this->pageHeader['bodyId'] = 'addPictures';
        
        if ($allParts)
        {
            $this->data['navigationBar'] = array(
                0 => array(
                    'text' => $this->pageHeader['title'],
                    'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl().'/addPictures'
                )
            );

            $this->data['naturals'] = $group->getNaturals();
        }
        else
        {
            $this->data['navigationBar'] = array(
                0 => array(
                    'text' => $this->pageHeader['title'],
                    'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl().'/'.$_SESSION['selection']['part']->getUrl().'/addPictures'
                )
            );

            $this->data['naturals'] = $part->getNaturals();
        }
        
        $this->data['returnUrl'] = 'menu/'.$class->getUrl().'/'.$group->getUrl();
        
        $this->view = 'addPictures';
    }
}