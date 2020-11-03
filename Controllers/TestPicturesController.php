<?php
/** 
 * Kontroler volaný pomocí AJAX, který zajišťuje odeslání adresy obrázků pro testovací stránku
 * @author Jan Štěch
 */
class TestPicturesController extends Controller
{
    private const PICTURES_SENT_PER_REQUEST = 20;
    
    /**
     * Metoda odesílající daný počet náhodně zvolených obrázků ze zvolené části/přírodniny
     * Adresy jsou odeslány jako pole v JSON formátu
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
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
        
        //Získání objektů obrázků
        if ($allParts)
        {
            $pictures = $group->getRandomPictures(self::PICTURES_SENT_PER_REQUEST);
        }
        else
        {
            $pictures = $part->getRandomPictures(self::PICTURES_SENT_PER_REQUEST);
        }
        
        //Vymazání předchozích odpovědí
        unset($_SESSION['testAnswers']);
        
        //Uložení nových odpovědí do $_SESSION a stavba dvourozměrného pole k odeslání
        $picturesArr = array();
        for ($i = 0; $i < count($pictures); $i++)
        {
            $picturesArr[] = array('num' => $i, 'url' => $pictures[$i]->getSrc());
            $_SESSION['testAnswers'][$i] = $pictures[$i]->getNatural()->getName();
        }
        
        //Odeslání dvourozměrného pole s čísly otázek a adresami obrázků
        header('Content-Type: application/json');
        echo json_encode($picturesArr);
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}