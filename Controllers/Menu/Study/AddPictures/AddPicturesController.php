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
        
        $this->pageHeader['title'] = 'Přidat obrázky';
        $this->pageHeader['description'] = 'Přidávejte obrázky do své poznávačky, aby se z nich mohli učit všichni členové třídy';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/ajaxMediator.js','js/addPictures.js', 'js/menu.js');
        $this->pageHeader['bodyId'] = 'addPictures';
        
        if ($allParts)
        {
            $this->data['naturals'] = $group->getNaturals();
        }
        else
        {
            $this->data['naturals'] = $part->getNaturals();
        }
        
        $this->data['returnUrl'] = 'menu/'.$class->getUrl().'/'.$group->getUrl();
        
        $this->view = 'addPictures';
    }
}