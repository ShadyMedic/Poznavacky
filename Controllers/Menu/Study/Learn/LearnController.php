<?php
namespace Poznavacky\Controllers\Menu\Study\Learn;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\MessageBox;
use Poznavacky\Models\Statics\UserManager;

/** 
 * Kontroler starající se o výpis stránky pro učení se
 * @author Jan Štěch
 */
class LearnController extends Controller
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

        $this->pageHeader['title'] = 'Učit se';
        $this->pageHeader['description'] = 'Učte se na poznávačku podle svého vlastního tempa';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/ajaxMediator.js','js/learn.js','js/reportForm.js', 'js/menu.js');
        $this->pageHeader['bodyId'] = 'learn';

        if ($allParts)
        {
            $this->data['navigationBar'] = array(
                0 => array(
                    'text' => $this->pageHeader['title'],
                    'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl().'/learn'
                )
            );

            $this->data['naturals'] = $group->getNaturals();
        }
        else
        {
            $this->data['navigationBar'] = array(
                0 => array(
                    'text' => $this->pageHeader['title'],
                    'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/'.$_SESSION['selection']['group']->getUrl().'/'.$_SESSION['selection']['part']->getUrl().'/learn'
                )
            );

            $this->data['naturals'] = $part->getNaturals();
        }

        $controllerName = "nonexistant-controller";
        if (isset($parameters[0])){ $controllerName = $this->kebabToCamelCase($parameters[0]).self::CONTROLLER_EXTENSION; }
        $pathToController = $this->controllerExists($controllerName);
        if ($pathToController)
        {
            //URL obsajuje požadavek na další kontroler používaný na learn stránce
            $this->controllerToCall = new $pathToController();
            $this->controllerToCall->process($parameters);
            
            $this->pageHeader['title'] = $this->controllerToCall->pageHeader['title'];
            $this->pageHeader['description'] = $this->controllerToCall->pageHeader['description'];
            $this->pageHeader['keywords'] = $this->controllerToCall->pageHeader['keywords'];
            $this->pageHeader['cssFiles'] = $this->controllerToCall->pageHeader['cssFiles'];
            $this->pageHeader['jsFiles'] = $this->controllerToCall->pageHeader['jsFiles'];
            $this->pageHeader['bodyId'] = $this->controllerToCall->pageHeader['bodyId'];
            $this->data['navigationBar'] = array_merge($this->data['navigationBar'], $this->controllerToCall->data['navigationBar']);
        }
        
        $this->view = 'learn';
    }
}

