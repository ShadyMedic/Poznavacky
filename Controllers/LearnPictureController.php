<?php
/** 
 * @author Kontroler volaný pomocí AJAX, který zajišťuje odeslání adresy obrázku pro učební stránku
 */
class LearnPictureController extends Controller
{

    /**
     * Metoda přijímající název přírodniny a získávající zdroje všech jejích obrázků z databáze
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        $naturalName = $_POST['name'];
        Db::connect();
        $pictures = Db::fetchQuery('SELECT zdroj FROM obrazky WHERE prirodniny_id = (SELECT prirodniny_id FROM prirodniny WHERE nazev = ? LIMIT 1)', array($naturalName), true);
        $picturesArr = array();
        foreach ($pictures as $picture)
        {
            $picturesArr[] = $picture['zdroj'];
        }
        echo json_encode($picturesArr);
        exit();
    }
}