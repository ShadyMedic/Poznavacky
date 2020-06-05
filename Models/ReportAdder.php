<?php
/** 
 * Třída starající se o zpracování dat odeslaných z formuláře pro přidání obrázku a případné uložení obrázku do databáze
 * @author Jan Štěch
 */
class ReportAdder
{
    private $group;
    
    /**
     * Konstruktor třídy nastavující poznávačku, z níž pochází nahlašovaný obrázek
     * Všechny ostatní údaje se předávají metodě proccessFormData - všechna taková data pocházejí z $_POST
     * @param Group $group Objekt poznávačky, z níž uživatel nahlašuje obrázek
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }
    
    /**
     * Metoda zpracovávající data odeslaná z formuláře pro nahlášení obrázku
     * Data jsou ověřena a posléze i uložena do databáze, nebo je vyvolána výjimka s chybovou hláškou
     * @param array $POSTdata Pole dat odeslaných z formuláře
     * @throws AccessDeniedException V případě že data nesplňují podmínky
     * @return boolean TRUE, pokud je úspěšně uloženo nové hlášení
     */
    public function processFormData(array $POSTdata)
    {
        $url = $_POST['picUrl'];
        $reason = $_POST['reason'];
        $additionalInformation = $_POST['info'];
        
        //TODO zvalidovat data
        
        //Získání objektu přírodniny
        Db::connect();
        $dbResult = Db::fetchQuery('
        SELECT
        casti.casti_id, casti.nazev AS "p_nazev", casti.prirodniny, casti.obrazky AS "p_obrazky",
        prirodniny.prirodniny_id, prirodniny.nazev AS "n_nazev", prirodniny.obrazky AS "n_obrazky",
        obrazky.obrazky_id, obrazky.prirodniny_id, obrazky.zdroj, obrazky.povoleno
        FROM obrazky
        JOIN prirodniny ON obrazky.prirodniny_id = prirodniny.prirodniny_id
        JOIN casti ON prirodniny.casti_id = casti.casti_id
        WHERE obrazky.zdroj = ? AND prirodniny.poznavacky_id = ?;
        ', array($url, $this->group->getId()), false);
        
        $part = new Part($dbResult['casti_id'], $dbResult['p_nazev'], $this->group, $dbResult['prirodniny'], $dbResult['p_obrazky']);
        $natural = new Natural($dbResult['prirodniny_id'], $dbResult['n_nazev'], $this->group, $part, $dbResult['n_obrazky']);
        $picture = new Picture($dbResult['obrazky_id'], $url, $natural, $dbResult['povoleno']);
        $report = new Report(0, $picture, $reason, $additionalInformation);
        $report->load();    //Zjištění, zda již takovéto hlášení v databázi existuje, popřípadě načtení jejich počtu
        $report->increaseReportersCount();  //Zvýšení počtu hlášení tohoto typu o 1
        $report->save();    //Uložení hlášení do databáze
    }
}