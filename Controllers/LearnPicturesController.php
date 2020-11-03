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
    public function process(array $parameters): void
    {
        $class = $_SESSION['selection']['class'];
        $group = $_SESSION['selection']['group'];
        $naturalName = $_POST['name'];
        
        $natural = new Natural(false);
        $natural->initialize($naturalName, null, null, $class);
        $pictures = $natural->getPictures();
        
        $picturesArr = array();
        foreach ($pictures as $picture)
        {
            $picturesArr[] = $picture->getSrc();
        }
        
        header('Content-Type: application/json');
        echo json_encode($picturesArr);
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}