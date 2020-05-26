<?php
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
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}