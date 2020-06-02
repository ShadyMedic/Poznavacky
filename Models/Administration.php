<?php

/** 
 * Třída získávající informace pro stránku se správou systému
 * V případě, že se tato třída příliš rozroste bude lepší ji rozdělit na více tříd
 * @author Jan Štěch
 */
class Administration
{
    /**
     * Metoda navracející většinu informací o všech uživatelích v databázi
     * @param bool $includeLogged TRUE, pokud má být navrácen i záznam přihlášeného uživatele
     * @return User[] Pole instancí třídy User
     */
    public function getAllUsers(bool $includeLogged = true)
    {
        Db::connect();
        if ($includeLogged)
        {
            $dbResult = Db::fetchQuery('SELECT uzivatele_id,jmeno,email,posledni_prihlaseni,pridane_obrazky,uhodnute_obrazky,karma,status FROM uzivatele', array(), true);
        }
        else
        {
            $dbResult = Db::fetchQuery('SELECT uzivatele_id,jmeno,email,posledni_prihlaseni,pridane_obrazky,uhodnute_obrazky,karma,status FROM uzivatele WHERE uzivatele_id != ?', array(UserManager::getId()), true);
        }
        $users = array();
        foreach($dbResult as $dbRow)
        {
            $lastLogin = new DateTime($dbRow['posledni_prihlaseni']);
            $users[] = new User($dbRow['uzivatele_id'], $dbRow['jmeno'], $dbRow['email'], $lastLogin, $dbRow['pridane_obrazky'], $dbRow['uhodnute_obrazky'], $dbRow['karma'], $dbRow['status']);
        }
        
        return $users;
    }
    
    /**
     * Metoda navracející informace o hlášeních obrázků, které byly nahlášeny z jednoho z důvodů, které musí řešit správce celého systému
     * Důvody, které musí být řešeny touto cestou jsou specifikovány v konstantách třídy Report
     * @return Report[] Pole instancí třídy Report
     */
    public function getAdminReports()
    {
        $in = str_repeat('?,', count(Report::ADMIN_REQUIRING_REASONS) - 1).'?'; 
        Db::connect();
        DebugLogger::debugLog($in);
        //Wow, zírejte na to. SQL dotaz, který vede přes většinu tabulek v databázi. To musí být výkonostní bomba!
        $result = Db::fetchQuery('
            SELECT
            hlaseni.hlaseni_id AS "hlaseni_id", hlaseni.duvod AS "hlaseni_duvod", hlaseni.dalsi_informace AS "hlaseni_dalsi_informace", hlaseni.pocet AS "hlaseni_pocet",
            obrazky.obrazky_id AS "obrazky_id", obrazky.zdroj AS "obrazky_zdroj", obrazky.povoleno AS "obrazky_povoleno",
            prirodniny.prirodniny_id AS "prirodniny_id", prirodniny.nazev AS "prirodniny_nazev", prirodniny.obrazky AS "prirodniny_obrazky",
            casti.casti_id AS "casti_id", casti.nazev AS "casti_nazev", casti.prirodniny AS "casti_prirodniny", casti.obrazky AS "casti_obrazky",
            poznavacky.poznavacky_id AS "poznavacky_id", poznavacky.nazev AS "poznavacky_nazev", poznavacky.casti AS "poznavacky_casti",
            tridy.tridy_id AS "tridy_id", tridy.nazev AS "tridy_nazev"
            FROM hlaseni
            JOIN obrazky ON hlaseni.obrazky_id = obrazky.obrazky_id
            JOIN prirodniny ON obrazky.prirodniny_id = prirodniny.prirodniny_id
            JOIN casti ON prirodniny.casti_id = casti.casti_id
            JOIN poznavacky ON casti.poznavacky_id = poznavacky.poznavacky_id
            JOIN tridy ON poznavacky.tridy_id = tridy.tridy_id
            WHERE hlaseni.duvod IN ('.$in.');
        ', Report::ADMIN_REQUIRING_REASONS, true);
        
        $reports = array();
        foreach ($result as $reportInfo)
        {
            //Následující kód indukuje, že jsem objektovou PHP aplikaci navrhl dobře, nebo úplně blbě...
            //V případě, že tohle bude po mně někdo muset předělávat... tak se ti ty nešťastníku omlouvám
            $class = new ClassObject($reportInfo['tridy_id'], $reportInfo['tridy_nazev']);
            $group = new Group($reportInfo['poznavacky_id'], $reportInfo['poznavacky_nazev'], $class, $reportInfo['poznavacky_casti']);
            $part = new Part($reportInfo['casti_id'], $reportInfo['casti_nazev'], $group, $reportInfo['casti_prirodniny'], $reportInfo['casti_obrazky']);
            $natural = new Natural($reportInfo['prirodniny_id'], $reportInfo['prirodniny_nazev'], $group, $part, $reportInfo['prirodniny_obrazky']);
            $picture = new Picture($reportInfo['obrazky_id'], $reportInfo['obrazky_zdroj'], $natural, $reportInfo['obrazky_povoleno']);
            $report = new Report($reportInfo['hlaseni_id'], $picture, $reportInfo['hlaseni_duvod'], $reportInfo['hlaseni_dalsi_informace'], $reportInfo['hlaseni_pocet']);
            $reports[] = $report;
        }
        
        return $reports;
    }
}