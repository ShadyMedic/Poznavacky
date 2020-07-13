<?php

/** 
 * Třída získávající informace pro stránku se správou systému
 * V případě, že se tato třída příliš rozroste bude lepší ji rozdělit na více tříd
 * @author Jan Štěch
 */
class Administration
{
    private const DANGEROUS_SQL_KEYWORDS = array(
        'ALTER ',
        'INDEX ',
        'DROP ',
        'TRIGGER ',
        'EVENT ',
        'ROUTINE ',
        'EXECUTE ',
        'GRANT ',
        'SUPER ',
        'PROCESS ',
        'RELOAD ',
        'SHUTDOWN ',
        'SHOW ',
        'LOCK ',
        'REFERENCES ',
        'REPLICATION ',
        'USER ');
    
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
    
    public function getAllClasses()
    {
        Db::connect();
        $dbResult = Db::fetchQuery('SELECT tridy_id, nazev, status, kod FROM tridy', array(), true);
        
        $classes = array();
        foreach($dbResult as $dbRow)
        {
            $classes[] = new ClassObject($dbRow['tridy_id'], $dbRow['nazev'], $dbRow['status'], $dbRow['kod']);
        }
        
        return $classes;
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
        
        if ($result === false)
        {
            //Žádná hlášení nenalezena
            return array();
        }
        
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
    
    /**
     * Metoda získávající seznam všech žádostí o změnu uživatelského jména a navrací je jako objekty
     * @return NameChangeRequest[] Pole objektů se žádostmi
     */
    public function getUserNameChangeRequests()
    {
        Db::connect();
        $result = Db::fetchQuery('
        SELECT
        uzivatele.uzivatele_id, uzivatele.jmeno, uzivatele.email, uzivatele.posledni_prihlaseni, uzivatele.pridane_obrazky, uzivatele.uhodnute_obrazky, uzivatele.karma, uzivatele.status,
        zadosti_jmena_uzivatele.zadosti_jmena_uzivatele_id, zadosti_jmena_uzivatele.nove, zadosti_jmena_uzivatele.cas
        FROM zadosti_jmena_uzivatele
        JOIN uzivatele ON zadosti_jmena_uzivatele.uzivatele_id = uzivatele.uzivatele_id;
        ', array(), true);
        
        //Kontrola, zda byly navráceny nějaké výsledky
        if ($result === false){ return array(); }
        
        $requests = array();
        foreach ($result as $requestInfo)
        {
            $user = new User($requestInfo['uzivatele_id'], $requestInfo['jmeno'], $requestInfo['email'], new DateTime($requestInfo['posledni_prihlaseni']), $requestInfo['pridane_obrazky'], $requestInfo['uhodnute_obrazky'], $requestInfo['karma'], $requestInfo['status']);
            $request = new NameChangeRequest($requestInfo['zadosti_jmena_uzivatele_id'], NameChangeRequest::TYPE_USER, $user, $requestInfo['nove'], new DateTime($requestInfo['cas']));
            $requests[] = $request;
        }
        return $requests;
    }
    
    public function getClassNameChangeRequests()
    {
        Db::connect();
        $result = Db::fetchQuery('
        SELECT
        uzivatele.uzivatele_id, uzivatele.jmeno, uzivatele.email, uzivatele.posledni_prihlaseni, uzivatele.pridane_obrazky, uzivatele.uhodnute_obrazky, uzivatele.karma, uzivatele.status AS "u_status",
        tridy.tridy_id, tridy.nazev, tridy.status AS "c_status", tridy.poznavacky, tridy.kod,
        zadosti_jmena_tridy.zadosti_jmena_tridy_id, zadosti_jmena_tridy.nove, zadosti_jmena_tridy.cas
        FROM zadosti_jmena_tridy
        JOIN tridy ON zadosti_jmena_tridy.tridy_id = tridy.tridy_id
        JOIN uzivatele ON tridy.spravce = uzivatele.uzivatele_id;
        ', array(), true);
        
        //Kontrola, zda byly navráceny nějaké výsledky
        if ($result === false){ return array(); }
        
        $requests = array();
        foreach ($result as $requestInfo)
        {
            $admin = new User($requestInfo['uzivatele_id'], $requestInfo['jmeno'], $requestInfo['email'], new DateTime($requestInfo['posledni_prihlaseni']), $requestInfo['pridane_obrazky'], $requestInfo['uhodnute_obrazky'], $requestInfo['karma'], $requestInfo['u_status']);
            $class = new ClassObject($requestInfo['tridy_id'], $requestInfo['nazev'], $requestInfo['c_status'], $requestInfo['kod'], $requestInfo['poznavacky'], $admin);
            $request = new NameChangeRequest($requestInfo['zadosti_jmena_tridy_id'], NameChangeRequest::TYPE_CLASS, $class, $requestInfo['nove'], new DateTime($requestInfo['cas']));
            
            $requests[] = $request;
        }
        return $requests;
    }
    
    /* Metody využívané AJAX kontrolerem AdministrateActionController */
    
    /**
     * Metoda upravující uživatelova data v databázi po jejich změně administrátorem
     * Je ověřeno, zda je přihlášený uživatel administrátorem a zda jsou zadané hodnoty platné
     * @param int $userId ID uživatele, jehož data mají být změněna
     * @param array $values Pole nových hodnot, podporované indexy jsou "addedPics", "guessedPics", "karma" a "status"
     */
    public function editUser(int $userId, array $values)
    {
        $user = new User($userId, 'null');  //Jméno (druhý argument) je sice povinné, ale vzhledem k tomu, že nebude potřeba a že tento objekt uživatele bude prakticky ihned zničen, můžeme využít tento malý hack
        $user->updateAccount($values['addedPics'], $values['guessedPics'], $values['karma'], $values['status']);
    }
    
    /**
     * Metoda odstraňující uživatelský účet a všechna jeho data z rozhodnutí administrátora
     * Je ověřeno, zda je přihlášený uživatel administrátorem a zda může být daný uživatel odstraněn
     * @param int $userId ID uživatele k odstranění
     */
    public function deleteUser(int $userId)
    {
        $user = new User($userId, 'null');  //Jméno (druhý argument) je sice povinné, ale vzhledem k tomu, že nebude potřeba a že tento objekt uživatele bude prakticky ihned zničen, můžeme využít tento malý hack
        $user->deleteAccountAsAdmin();
        unset($user);
    }
    
    /**
     * Metoda upravující přístupová data třídy v databázi po jejich změně administrátorem
     * Je ověřeno, zda je přihlášený uživatel administrátorem a zda jsou zadané hodnoty platné
     * @param int $classId ID třídy, jejíž data mají být změněna
     * @param array $values Pole nových hodnot, podporované indexy jsou "status" a "code"
     */
    public function editClass(int $classId, array $values)
    {
        $class = new ClassObject($classId, 'null');  //Jméno (druhý argument) je sice povinné, ale vzhledem k tomu, že nebude potřeba a že tento objekt třídy bude prakticky ihned zničen, můžeme využít tento malý hack
        $class->updateAccessData($values['status'], $values['code']);
    }
    
    /**
     * Metoda měnící správce třídy v databázi po jeho změně administrátorem
     * Je ověřeno, zda je přihlášený uživatel administrátorem a zda jsou zadané hodnoty platné
     * @param int $classId ID třídy, jejíž správce je měněn
     * @param int|string $newAdminIdentifier ID nebo jméno nového správce třídy
     * @param string $changedIdentifier Údaj o tom, zda je druhý argument této metody ID nového správce třídy, nebo jeho jméno
     * @throws AccessDeniedException Pokud není některý z údajů platný (například pokud uživatel s daným ID nebo jménem neexistuje)
     * @return User Objekt uživatele reprezentující právě nastaveného správce třídy
     */
    public function changeClassAdmin(int $classId, $newAdminIdentifier, string $changedIdentifier)
    {
        //Konstrukce objektu uživatele
        Db::connect();
        switch ($changedIdentifier)
        {
            case 'id':
                $result = Db::fetchQuery('SELECT uzivatele_id, jmeno, email, posledni_prihlaseni, pridane_obrazky, uhodnute_obrazky, karma, status FROM uzivatele WHERE uzivatele_id = ?', array($newAdminIdentifier), false);
                break;
            case 'name':
                $result = Db::fetchQuery('SELECT uzivatele_id, jmeno, email, posledni_prihlaseni, pridane_obrazky, uhodnute_obrazky, karma, status FROM uzivatele WHERE jmeno = ?', array($newAdminIdentifier), false);
                break;
            default:
                throw new AccessDeniedException(AccessDeniedException::REASON_ADMINISTRATION_CLASS_ADMIN_UPDATE_INVALID_IDENTIFIER);
                break;
        }
        if ($result === false)
        {
            //Uživatel nenalezen
            throw new AccessDeniedException(AccessDeniedException::REASON_ADMINISTRATION_CLASS_ADMIN_UPDATE_UNKNOWN_USER);
        }
        
        $admin = new User($result['uzivatele_id'], $result['jmeno'], $result['email'], new DateTime($result['posledni_prihlaseni']), $result['pridane_obrazky'], $result['uhodnute_obrazky'], $result['karma'], $result['status']);
        
        $class = new ClassObject($classId, 'null');   //Jméno (druhý argument) je sice povinné, ale vzhledem k tomu, že nebude potřeba a že tento objekt třídy bude prakticky ihned zničen, můžeme využít tento malý hack
        $class->changeClassAdminAsAdmin($admin);
        return $admin;
    }
    
    /**
     * Metoda odstraňující třídu z databáze společně se všemi jejími poznávačkami, skupinami, přírodninami, obrázky a hlášeními
     * @param int $classId ID třídy k odstranění
     */
    public function deleteClass(int $classId)
    {
        $class = new ClassObject($classId, 'null');  //Jméno (druhý argument) je sice povinné, ale vzhledem k tomu, že nebude potřeba a že tento objekt třídy bude prakticky ihned zničen, můžeme využít tento malý hack
        $class->deleteAsAdmin();
        unset($class);
    }
    
    /**
     * Metoda skrývající obrázek s daným ID z databáze i se všemi jeho hlášeními
     * @param int $pictureId ID obrázku k odstranění
     */
    public function disablePicture(int $pictureId)
    {
        $picture = new Picture($pictureId);
        $picture->disable();
        $picture->deleteReports();
    }
    
    /**
     * Metoda odstraňující obrázek s daným ID z databáze i se všemi jeho hlášeními
     * @param int $pictureId ID obrázku k odstranění
     */
    public function deletePicture(int $pictureId)
    {
        $picture = new Picture($pictureId);
        $picture->delete();
        unset($picture);
    }
    
    /**
     * Metoda odstraňující hlášení s daným ID z databáze
     * @param int $reportId ID hlášení k odstranění
     */
    public function deleteReport(int $reportId)
    {
        $report = new Report($reportId);
        $report->delete();
        unset($report);
    }
    
    /**
     * Metoda řešící vyřízení žádosti o změnu jména uživatele nebo třídy
     * V případě schválení je jméno uživatele nebo třídy změněno
     * V obou případech je žádost odstraněna z databáze
     * Uživatel následně obdrží e-mail s verdiktem (pokud jej zadal)
     * @param int $requestId ID žádosti z databáze
     * @param bool $classNameChange TRUE, pokud se žádost týká změny jména třídy, FALSE, pokud změny uživatelského jména
     * @param bool $approved TRUE, pokud byla žádost schválena, FALSE, pokud zamítnuta
     * @param string $reason V případě zamítnutí žádosti důvod jejího zamítnutí - je odesláno e-mailem uživateli; při schválení žádosti nepovinné
     */
    public function resolveNameChange(int $requestId, bool $classNameChange, bool $approved, string $reason = "")
    {
        $type = ($classNameChange) ? NameChangeRequest::TYPE_CLASS : NameChangeRequest::TYPE_USER;
        $request = new NameChangeRequest($requestId, $type);
        if ($approved)
        {
            $request->approve();
        }
        else
        {
            $request->decline($reason);
        }
        $request->erase();
        unset($request);
    }
    
    /**
     * Metoda vkládající HTML e-mailovou zprávu z formuláře v záložce "Poslat e-mail" do připravené šablony a navrací výsledné HTML
     * @param string $rawMessage Obsah hlavního těla e-mailu (může být zformátován pomocí HTML)
     * @param string $rawFooter Obsah patičky e-mailu (může být zformátován pomocí HTML)
     * @return string Kompletní HTML těla e-mailu, které by bylo odesláno
     */
    public function previewEmail(string $rawMessage, string $rawFooter)
    {
        //Převod konců řádků na zobrazitelné <br> tagy
        $rawMessage = nl2br($rawMessage);
        $rawFooter = nl2br($rawFooter);
        
        $emailComposer = new EmailComposer();
        $emailComposer->composeMail($emailComposer::EMAIL_TYPE_EMPTY_LAYOUT, array('content' => $rawMessage, 'footer' => $rawFooter));
        return $emailComposer->getMail();
    }
    
    /**
     * Metoda odesílající e-mail s daty z formuláře na správcovské stránce
     * @param string $addressee E-mailová adresa adresáta e-mailu
     * @param string $subject Předmět e-mailu
     * @param string $rawMessage Obsah hlavního těla e-mailu (může být zformátován pomocí HTML)
     * @param string $rawFooter Obsah patičky e-mailu (může být zformátován pomocí HTML), může být jako jediný parametr prázdný řetězec
     * @param string $sender Jméno odesílatele e-mailu, které bude zobrazeno jako odesílatel e-mailové zprávy
     * @param string $fromAddress E-mailová adresa pro odpověď (bude to tak trochu vypadat, jako kdyby e-mail přišel z této adresy)
     * @throws AccessDeniedException V případě, že některý z parametrů je nedostatečně nebo chybně vyplněn
     * @return boolean TRUE, pokud se e-mail podaří odeslat
     */
    public function sendEmail(string $addressee, string $subject, string $rawMessage, string $rawFooter, string $sender, string $fromAddress)
    {
        //Kontrola platnosti e-mailů
        if (!filter_var($addressee, FILTER_VALIDATE_EMAIL)){ throw new AccessDeniedException(AccessDeniedException::REASON_SEND_EMAIL_INVALID_ADDRESSEE_ADDRESS, null, null, array('originalFile' => 'Administration.php', 'displayOnView' => 'administrate.phtml')); }
        if (!filter_var($fromAddress, FILTER_VALIDATE_EMAIL)){ throw new AccessDeniedException(AccessDeniedException::REASON_SEND_EMAIL_INVALID_SENDER_ADDRESS, null, null, array('originalFile' => 'Administration.php', 'displayOnView' => 'administrate.phtml')); }
        
        //Kontrola vyplněnosti ostatních polí
        if (mb_strlen($subject) === 0 || mb_strlen($rawMessage) === 0 || mb_strlen($sender) === 0){ throw new AccessDeniedException(AccessDeniedException::REASON_SEND_EMAIL_EMPTY_FIELDS, null, null, array('originalFile' => 'Administration.php', 'displayOnView' => 'administrate.phtml')); }
        
        $emailBody = $this->previewEmail($rawMessage, $rawFooter);
        $emailSender = new EmailSender();
        return $emailSender->sendMail($addressee, $subject, $emailBody, $fromAddress, $sender);
    }
    
    /**
     * Metoda vykonávající zadané SQL dotazy a navracející jeho výsledky jako HTML
     * @param string $queries SQL dotaz/y, v případě více dotazů musí být ukončeny středníky
     * @return string Zformátovaný výstup dotazu jako HTML určené k zobrazení uživateli
     */
    public function executeSqlQueries(string $queries)
    {
        //Kontrola pro nebezpečná klíčová slova
        $tempQuery = strtoupper($queries);
        $cnt = count(self::DANGEROUS_SQL_KEYWORDS);
        for ($i = 0; $i < $cnt; $i++)
        {
            if (strpos($tempQuery, self::DANGEROUS_SQL_KEYWORDS[$i]) !== false)
            {
                $word = self::DANGEROUS_SQL_KEYWORDS[$i];
                return "<p>Váš příkaz obsahuje nebezpečné klíčové slovo (<b>$word</b>). Z toho důvodu byl příkaz zablokován.</p>";
            }
        }
        
        //Kontrola OK
        
        ob_start();
        
        $queries = rtrim($queries, ';'); //Odebrání posledního střeníku (pokud exisutje), aby následující příkaz vygeneroval čisté pole jednotlivých dotazů
        $queries = explode(';',$queries); //Pro případ, že je zadáno více příkazů.
        
        Db::connect();
        
        $cnt = count($queries);
        if (empty($cnt) && !empty($queries)){$cnt++;}     //Pokud není přítomen žádný středník (a byl odeslán nějaký text), provedeme ten jeden jediný, co nekončí středníkem
        for ($i = 0; $i < $cnt; $i++)
        {
            echo '<p>';
            $queryResult = Db::unpreparedQuery($queries[$i]);
            if (gettype($queryResult) === 'boolean')
            {
                //Výsledek není tabulka
                if ($queryResult)
                {
                    echo "Dotaz <i>$queries[$i]</i> byl úspěšně proveden.";
                }
                else
                {
                    echo "Při provádění dozazu <i>$queries[$i]</i> došlo k chybě.";
                }
            }
            else
            {
                //Výsledek je tabulka
                echo "Dotaz <i>$queries[$i]</i> byl úspěšně proveden, byly navráceny následující výsledky:";
                echo '<table>';
                
                //Vypsání hlavičky tabulky
                $returnedRow = $queryResult[0];
                echo '<tr>';
                foreach ($returnedRow as $returnedColumnName => $returnedCell)
                {
                    //Přeskočené sloupců oindexovanými čísly
                    if (gettype($returnedColumnName) === 'integer'){ continue; }
                    
                    echo '<th>';
                    echo $returnedColumnName;
                    echo '</th>';
                }
                echo '</tr>';
                
                //Vypsání těla tabulky
                foreach ($queryResult as $returnedRow)
                {   
                    echo '<tr>';
                    foreach ($returnedRow as $returnedColumnName => $returnedCell)
                    {
                        //Přeskočení sloupců oindexovanými čísly (každý sloupec se v poli vyskytuje dvakrát - jednou indexovaný číslem a jednou názvem sloupce)
                        if (gettype($returnedColumnName) === 'integer'){ continue; }
                        
                        echo '<td>';
                        echo $returnedCell;
                        echo '</td>';
                    }
                    echo '</tr>';
                }
                echo '</table>';
            }
            echo '</p>';
        }
        
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
}