<?php
namespace Poznavacky\Controllers\Menu\Study\Learn;

use Poznavacky\Controllers\Controller;
use Poznavacky\Models\DatabaseItems\Natural;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\AjaxResponse;

/** 
 * Kontroler volaný pomocí AJAX, který zajišťuje odeslání adresy obrázků pro učební stránku
 * @author Jan Štěch
 */
class LearnPicturesController extends Controller
{

    /**
     * Metoda přijímající název přírodniny skrz $_POST a získávající zdroje všech jejích obrázků z databáze
     * Adresy jsou odeslány jako pole v JSON formátu
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        //Kontrola přístupu
        $class = $_SESSION['selection']['class'];
        if (!$class->checkAccess(UserManager::getId()))
        {
            header('HTTP/1.0 403 Forbidden');
            exit();
        }

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

        //Kontrola přítomnosti přírodnin
        if (
            ($allParts && count($group->getNaturals()) === 0) ||
            (!$allParts && $part->getNaturalsCount() === 0)
        )
        {
            //Žádné přírodniny
            header('HTTP/1.0 400 Bad Request');
            exit();
        }

        $naturalName = urldecode($_GET['natural']);
        
        $natural = new Natural(false);
        $natural->initialize($naturalName, null, null, $class);
        $pictures = $natural->getPictures();
        
        $picturesArr = array();
        foreach ($pictures as $picture)
        {
            $picturesArr[] = $picture->getSrc();
        }
        
        header('Content-Type: application/json');
        $response = new AjaxResponse(AjaxResponse::MESSAGE_TYPE_SUCCESS, '', array('pictures' => $picturesArr));
        echo $response->getResponseString();
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}

