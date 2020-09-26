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
        
        //Kontrola, zda je zadaný důvod platný
        $availableReasons = REPORT::ALL_REASONS;
        
        if (!in_array($reason, $availableReasons, true))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_REPORT_INVALID_REASON, null, null, array('originalFile' => 'ReportAdder.php', 'displayOnView' => 'learn.phtml|test.phtml'));
        }
        
        //Kontrola vyplnění dodatečných informací (jsou-li potřeba)
        if ($reason === Report::REASON_LONG_LOADING)
        {
            //Kontrola, zda je specifikován jeden z časových intervalů
            if (!in_array($additionalInformation, Report::LONG_LOADING_AVAILABLE_DELAYS))
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_REPORT_INVALID_ADDITIONAL_INFORMATION, null, null, array('originalFile' => 'ReportAdder.php', 'displayOnView' => 'learn.phtml|test.phtml'));
            }
        }
        if ($reason === Report::REASON_OTHER || $reason === Report::REASON_OTHER_ADMIN)
        {
            if (!mb_strlen($additionalInformation) > 0)
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_REPORT_INVALID_ADDITIONAL_INFORMATION, null, null, array('originalFile' => 'ReportAdder.php', 'displayOnView' => 'learn.phtml|test.phtml'));
            }
        }
        
        //Nastavit správnou přírodninu na "Nezadáno" v případě, že důvod nahlášení je nesprávná přírodnina, ale nebylo zadáno, jaká je správná
        if ($reason === Report::REASON_INCORRECT_NATURAL && mb_strlen($additionalInformation) === 0)
        {
            $additionalInformation = Report::INCORRECT_NATURAL_DEFAULT_INFO;
        }
        
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
        
        //Obrázek nebyl v databázi podle zdroje nalezen
        if ($dbResult === false)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_REPORT_UNKNOWN_PICTURE, null, null, array('originalFile' => 'ReportAdder.php', 'displayOnView' => 'learn.phtml|test.phtml'));
        }
        
        $part = new Part(false, $dbResult['casti_id']);
        $part->initialize($dbResult['p_nazev'], $this->group, null, $dbResult['prirodniny'], $dbResult['p_obrazky']);
        $natural = new Natural(false, $dbResult['prirodniny_id']);
        $natural->initialize($dbResult['n_nazev'], null, $dbResult['n_obrazky'], null, $this->group, $part);
        $picture = new Picture(false, $dbResult['obrazky_id']);
        $picture->initialize($url, $natural, $part, $dbResult['povoleno'], null);
        
        $report = new Report(false, 0);    //Pokus s hlášením, které již v datbázi existuje, ale u kterého neznáme ID
        $report->initialize($picture, $reason, $additionalInformation, null);
        try
        {
            $report->load();    //Pokud hlášení zatím v databázi neexistuje, je vyvolána výjimka typu NoDataException
            $report->increaseReportersCount();  //Zvýšení počtu hlášení tohoto typu o 1
        }
        catch (NoDataException $e)
        {
            $report = new Report(true); //Tvorba nového hlášení
            $report->initialize($picture, $reason, $additionalInformation, 1);
        }
        $report->save();    //Uložení hlášení do databáze
    }
}