<?php
/** 
 * @author Kontroler volaný pomocí AJAX, který zajišťuje odeslání adresy obrázku pro učební stránku
 */
class LearnPicturesController extends Controller
{

    /**
     * Metoda přijímající název přírodniny a získávající zdroje všech jejích obrázků z databáze
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        $className = $parameters['class'];
        $groupName = $parameters['group'];
        $naturalName = $_POST['name'];
        
        Db::connect();
        $pictures = Db::fetchQuery('SELECT zdroj FROM obrazky WHERE prirodniny_id = (SELECT prirodniny_id FROM prirodniny WHERE nazev = ? AND poznavacky_id = (SELECT poznavacky_id FROM poznavacky WHERE nazev = ?)AND poznavacky_id IN (SELECT poznavacky_id FROM poznavacky WHERE tridy_id = (SELECT tridy_id FROM tridy WHERE nazev = ?)) LIMIT 1);', array($naturalName, $groupName, $className), true);
        
        $picturesArr = array();
        foreach ($pictures as $picture)
        {
            $picturesArr[] = $picture['zdroj'];
        }
        echo json_encode($picturesArr);
        exit();
    }
}