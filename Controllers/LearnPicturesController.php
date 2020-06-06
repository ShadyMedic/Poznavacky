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
        $group = $_SESSION['selection']['group'];
        $naturalName = $_POST['name'];
        
        $natural = new Natural(0, $naturalName, $group);
        $pictures = $natural->getPictures();
        
        $picturesArr = array();
        foreach ($pictures as $picture)
        {
            $picturesArr[] = $picture['src'];
        }
        echo json_encode($picturesArr);
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}