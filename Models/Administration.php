<?php
namespace Poznavacky\Models;

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
     * Konstruktor zajišťující, že instanci této třídy lze vytvořit pouze pokud je přihlášen administrátor
     * @throws AccessDeniedException V případě, že není přihlášen administrátor
     */
    public function __construct()
    {
        $aChecker = new AccessChecker();
        if (!$aChecker->checkSystemAdmin())
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
    }
    
    /**
     * Metoda navracející většinu informací o všech uživatelích v databázi
     * @param bool $includeLogged TRUE, pokud má být navrácen i záznam přihlášeného uživatele
     * @return User[] Pole instancí třídy User
     */
    public function getAllUsers(bool $includeLogged = true): array
    {
        if ($includeLogged)
        {
            $dbResult = Db::fetchQuery('SELECT '.User::COLUMN_DICTIONARY['id'].','.User::COLUMN_DICTIONARY['name'].','.User::COLUMN_DICTIONARY['email'].','.User::COLUMN_DICTIONARY['lastLogin'].','.User::COLUMN_DICTIONARY['addedPictures'].','.User::COLUMN_DICTIONARY['guessedPictures'].','.User::COLUMN_DICTIONARY['guessedPictures'].','.User::COLUMN_DICTIONARY['karma'].','.User::COLUMN_DICTIONARY['status'].' FROM '.User::TABLE_NAME, array(), true);
        }
        else
        {
            $dbResult = Db::fetchQuery('SELECT '.User::COLUMN_DICTIONARY['id'].','.User::COLUMN_DICTIONARY['name'].','.User::COLUMN_DICTIONARY['email'].','.User::COLUMN_DICTIONARY['lastLogin'].','.User::COLUMN_DICTIONARY['addedPictures'].','.User::COLUMN_DICTIONARY['guessedPictures'].','.User::COLUMN_DICTIONARY['guessedPictures'].','.User::COLUMN_DICTIONARY['karma'].','.User::COLUMN_DICTIONARY['status'].' FROM '.User::TABLE_NAME.' WHERE '.User::COLUMN_DICTIONARY['id'].' != ?', array(UserManager::getId()), true);
        }
        $users = array();
        foreach($dbResult as $dbRow)
        {
            $lastLogin = new DateTime($dbRow[User::COLUMN_DICTIONARY['lastLogin']]);
            $user = new User(false, $dbRow[User::COLUMN_DICTIONARY['id']]);
            $user->initialize($dbRow[User::COLUMN_DICTIONARY['name']], $dbRow[User::COLUMN_DICTIONARY['email']], $lastLogin, $dbRow[User::COLUMN_DICTIONARY['addedPictures']], $dbRow[User::COLUMN_DICTIONARY['guessedPictures']], $dbRow[User::COLUMN_DICTIONARY['karma']], $dbRow[User::COLUMN_DICTIONARY['status']]);
            $users[] = $user;
        }
        
        return $users;
    }
    
    /**
     * Metoda navracející pole všech tříd uložených v databázi jako objekty
     * @return array Pole objektů tříd
     */
    public function getAllClasses(): array
    {
        $dbResult = Db::fetchQuery('SELECT '.ClassObject::COLUMN_DICTIONARY['id'].','.ClassObject::COLUMN_DICTIONARY['name'].','.ClassObject::COLUMN_DICTIONARY['url'].','.ClassObject::COLUMN_DICTIONARY['groupsCount'].','.ClassObject::COLUMN_DICTIONARY['status'].','.ClassObject::COLUMN_DICTIONARY['code'].','.ClassObject::COLUMN_DICTIONARY['admin'].' FROM '.ClassObject::TABLE_NAME, array(), true);
        
        $classes = array();
        foreach($dbResult as $dbRow)
        {
            $class = new ClassObject(false, $dbRow[ClassObject::COLUMN_DICTIONARY['id']]);
            $class->initialize($dbRow[ClassObject::COLUMN_DICTIONARY['name']], $dbRow[ClassObject::COLUMN_DICTIONARY['url']], $dbRow[ClassObject::COLUMN_DICTIONARY['status']], $dbRow[ClassObject::COLUMN_DICTIONARY['code']], null, $dbRow[ClassObject::COLUMN_DICTIONARY['groupsCount']], null, new User(false, $dbRow[ClassObject::COLUMN_DICTIONARY['admin']]));
            $classes[] = $class;
        }
        
        return $classes;
    }
    
    /**
     * Metoda navracející informace o hlášeních obrázků, které byly nahlášeny z jednoho z důvodů, které musí řešit správce celého systému
     * Důvody, které musí být řešeny touto cestou jsou specifikovány v konstantách třídy Report
     * @return Report[] Pole instancí třídy Report
     */
    public function getAdminReports(): array
    {
        $in = str_repeat('?,', count(Report::ADMIN_REQUIRING_REASONS) - 1).'?'; 
        $result = Db::fetchQuery('
            SELECT
            '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['id'].' AS "hlaseni_id", '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['reason'].' AS "hlaseni_duvod", '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['additionalInformation'].' AS "hlaseni_dalsi_informace", '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['reportersCount'].' AS "hlaseni_pocet",
            '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['id'].' AS "obrazky_id", '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['src'].' AS "obrazky_zdroj", '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['enabled'].' AS "obrazky_povoleno",
            '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['id'].' AS "prirodniny_id", '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['name'].' AS "prirodniny_nazev", '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['picturesCount'].' AS "prirodniny_obrazky"
            FROM '.Report::TABLE_NAME.'
            JOIN '.Picture::TABLE_NAME.' ON '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['picture'].' = '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['id'].'
            JOIN '.Natural::TABLE_NAME.' ON '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['natural'].' = '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['id'].'
            WHERE '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['reason'].' IN ('.$in.');
        ', Report::ADMIN_REQUIRING_REASONS, true);
        
        if ($result === false)
        {
            //Žádná hlášení nenalezena
            return array();
        }
        
        $reports = array();
        foreach ($result as $reportInfo)
        {
            $natural = new Natural(false, $reportInfo['prirodniny_id']);
            $natural->initialize($reportInfo['prirodniny_nazev'], null, $reportInfo['prirodniny_obrazky'], null);
            $picture = new Picture(false, $reportInfo['obrazky_id']);
            $picture->initialize($reportInfo['obrazky_zdroj'], $natural, $reportInfo['obrazky_povoleno'], null);
            $report = new Report(false, $reportInfo['hlaseni_id']);
            $report->initialize($picture, $reportInfo['hlaseni_duvod'], $reportInfo['hlaseni_dalsi_informace'], $reportInfo['hlaseni_pocet']);
            $reports[] = $report;
        }
        
        return $reports;
    }
    
    /**
     * Metoda získávající seznam všech žádostí o změnu uživatelského jména a navrací je jako objekty
     * @return UserNameChangeRequest[] Pole objektů se žádostmi
     */
    public function getUserNameChangeRequests(): array
    {
        $result = Db::fetchQuery('
        SELECT
        '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['id'].', '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['name'].', '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['email'].', '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['lastLogin'].', '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['addedPictures'].', '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['guessedPictures'].', '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['karma'].', '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['status'].',
        '.UserNameChangeRequest::TABLE_NAME.'.'.UserNameChangeRequest::COLUMN_DICTIONARY['id'].', '.UserNameChangeRequest::TABLE_NAME.'.'.UserNameChangeRequest::COLUMN_DICTIONARY['newName'].', '.UserNameChangeRequest::TABLE_NAME.'.'.UserNameChangeRequest::COLUMN_DICTIONARY['requestedAt'].'
        FROM '.UserNameChangeRequest::TABLE_NAME.'
        JOIN '.User::TABLE_NAME.' ON '.UserNameChangeRequest::TABLE_NAME.'.'.UserNameChangeRequest::COLUMN_DICTIONARY['subject'].' = '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['id'].';
        ', array(), true);
        
        //Kontrola, zda byly navráceny nějaké výsledky
        if ($result === false){ return array(); }
        
        $requests = array();
        foreach ($result as $requestInfo)
        {
            $user = new User(false, $requestInfo[User::COLUMN_DICTIONARY['id']]);
            $user->initialize($requestInfo[User::COLUMN_DICTIONARY['name']], $requestInfo[User::COLUMN_DICTIONARY['email']], new DateTime($requestInfo[User::COLUMN_DICTIONARY['lastLogin']]), $requestInfo[User::COLUMN_DICTIONARY['addedPictures']], $requestInfo[User::COLUMN_DICTIONARY['guessedPictures']], $requestInfo[User::COLUMN_DICTIONARY['karma']], $requestInfo[User::COLUMN_DICTIONARY['status']]);
            $request = new UserNameChangeRequest(false, $requestInfo[UserNameChangeRequest::COLUMN_DICTIONARY['id']]);
            $request->initialize($user, $requestInfo[UserNameChangeRequest::COLUMN_DICTIONARY['newName']], new DateTime($requestInfo[UserNameChangeRequest::COLUMN_DICTIONARY['requestedAt']]));
            $requests[] = $request;
        }
        return $requests;
    }
    
    /**
     * Metoda získávající seznam všech žádostí o změnu názvu třídy a navrací je jako objekty
     * @return ClassNameChangeRequest[] Pole objektů se žádostmi
     */
    public function getClassNameChangeRequests(): array
    {
        $result = Db::fetchQuery('
        SELECT
        '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['id'].', '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['name'].', '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['email'].', '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['lastLogin'].', '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['addedPictures'].', '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['guessedPictures'].', '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['karma'].', '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['status'].' AS "u_status",
        '.ClassObject::TABLE_NAME.'.'.ClassObject::COLUMN_DICTIONARY['id'].', '.ClassObject::TABLE_NAME.'.'.ClassObject::COLUMN_DICTIONARY['name'].', '.ClassObject::TABLE_NAME.'.'.ClassObject::COLUMN_DICTIONARY['url'].', '.ClassObject::TABLE_NAME.'.'.ClassObject::COLUMN_DICTIONARY['status'].' AS "c_status", '.ClassObject::TABLE_NAME.'.'.ClassObject::COLUMN_DICTIONARY['groupsCount'].', '.ClassObject::TABLE_NAME.'.'.ClassObject::COLUMN_DICTIONARY['code'].',
        '.ClassNameChangeRequest::TABLE_NAME.'.'.ClassNameChangeRequest::COLUMN_DICTIONARY['id'].', '.ClassNameChangeRequest::TABLE_NAME.'.'.ClassNameChangeRequest::COLUMN_DICTIONARY['newName'].', '.ClassNameChangeRequest::TABLE_NAME.'.'.ClassNameChangeRequest::COLUMN_DICTIONARY['requestedAt'].'
        FROM '.ClassNameChangeRequest::TABLE_NAME.'
        JOIN '.ClassObject::TABLE_NAME.' ON '.ClassNameChangeRequest::TABLE_NAME.'.'.ClassNameChangeRequest::COLUMN_DICTIONARY['subject'].' = '.ClassObject::TABLE_NAME.'.'.ClassObject::COLUMN_DICTIONARY['id'].'
        JOIN '.User::TABLE_NAME.' ON '.ClassObject::TABLE_NAME.'.'.ClassObject::COLUMN_DICTIONARY['admin'].' = '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['id'].';
        ', array(), true);
        
        //Kontrola, zda byly navráceny nějaké výsledky
        if ($result === false){ return array(); }
        
        $requests = array();
        foreach ($result as $requestInfo)
        {
            $admin = new User(false, $requestInfo[User::COLUMN_DICTIONARY['id']]);
            $admin->initialize($requestInfo[User::COLUMN_DICTIONARY['name']], $requestInfo[User::COLUMN_DICTIONARY['email']], new DateTime($requestInfo[User::COLUMN_DICTIONARY['lastLogin']]), $requestInfo[User::COLUMN_DICTIONARY['addedPictures']], $requestInfo[User::COLUMN_DICTIONARY['guessedPictures']], $requestInfo[User::COLUMN_DICTIONARY['karma']], $requestInfo['u_status']);
            $class = new ClassObject(false, $requestInfo[ClassObject::COLUMN_DICTIONARY['id']]);
            $class->initialize($requestInfo[ClassObject::COLUMN_DICTIONARY['name']], $requestInfo[ClassObject::COLUMN_DICTIONARY['url']], $requestInfo['c_status'], $requestInfo[ClassObject::COLUMN_DICTIONARY['code']], null, $requestInfo[ClassObject::COLUMN_DICTIONARY['groupsCount']], null, $admin);
            $request = new ClassNameChangeRequest(false, $requestInfo[ClassNameChangeRequest::COLUMN_DICTIONARY['id']]);
            $request->initialize($class, $requestInfo[ClassNameChangeRequest::COLUMN_DICTIONARY['newName']], new DateTime($requestInfo[ClassNameChangeRequest::COLUMN_DICTIONARY['requestedAt']]));
            
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
    public function editUser(int $userId, array $values): void
    {
        $user = new User(false, $userId);
        $user->updateAccount($values['addedPics'], $values['guessedPics'], $values[User::COLUMN_DICTIONARY['karma']], $values['status']);
    }
    
    /**
     * Metoda odstraňující uživatelský účet a všechna jeho data z rozhodnutí administrátora
     * Je ověřeno, zda je přihlášený uživatel administrátorem a zda může být daný uživatel odstraněn
     * @param int $userId ID uživatele k odstranění
     */
    public function deleteUser(int $userId): void
    {
        $user = new User(false, $userId);
        $user->deleteAccountAsAdmin();
    }
    
    /**
     * Metoda upravující přístupová data třídy v databázi po jejich změně administrátorem
     * Je ověřeno, zda je přihlášený uživatel administrátorem a zda jsou zadané hodnoty platné
     * @param int $classId ID třídy, jejíž data mají být změněna
     * @param array $values Pole nových hodnot, podporované indexy jsou "status" a "code"
     */
    public function editClass(int $classId, array $values): void
    {
        $class = new ClassObject(false, $classId);
        $class->updateAccessDataAsAdmin($values['status'], $values['code']);
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
    public function changeClassAdmin(int $classId, $newAdminIdentifier, string $changedIdentifier): User
    {
        //Konstrukce objektu uživatele
        switch ($changedIdentifier)
        {
            case 'id':
                $result = Db::fetchQuery('SELECT '.User::COLUMN_DICTIONARY['id'].', '.User::COLUMN_DICTIONARY['name'].', '.User::COLUMN_DICTIONARY['email'].', '.User::COLUMN_DICTIONARY['lastLogin'].', '.User::COLUMN_DICTIONARY['addedPictures'].', '.User::COLUMN_DICTIONARY['guessedPictures'].', '.User::COLUMN_DICTIONARY['karma'].', '.User::COLUMN_DICTIONARY['status'].' FROM '.User::TABLE_NAME.' WHERE '.User::COLUMN_DICTIONARY['id'].' = ?', array($newAdminIdentifier), false);
                break;
            case 'name':
                $result = Db::fetchQuery('SELECT '.User::COLUMN_DICTIONARY['id'].', '.User::COLUMN_DICTIONARY['name'].', '.User::COLUMN_DICTIONARY['email'].', '.User::COLUMN_DICTIONARY['lastLogin'].', '.User::COLUMN_DICTIONARY['addedPictures'].', '.User::COLUMN_DICTIONARY['guessedPictures'].', '.User::COLUMN_DICTIONARY['karma'].', '.User::COLUMN_DICTIONARY['status'].' FROM '.User::TABLE_NAME.' WHERE '.User::COLUMN_DICTIONARY['name'].' = ?', array($newAdminIdentifier), false);
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
        
        $admin = new User(false, $result[User::COLUMN_DICTIONARY['id']]);
        $admin->initialize($result[User::COLUMN_DICTIONARY['name']], $result[User::COLUMN_DICTIONARY['email']], new DateTime($result[User::COLUMN_DICTIONARY['lastLogin']]), $result[User::COLUMN_DICTIONARY['addedPictures']], $result[User::COLUMN_DICTIONARY['guessedPictures']], $result[User::COLUMN_DICTIONARY['karma']], $result[User::COLUMN_DICTIONARY['status']]);
        
        $class = new ClassObject(false, $classId);
        $class->changeClassAdminAsAdmin($admin);
        return $admin;
    }
    
    /**
     * Metoda odstraňující třídu z databáze společně se všemi jejími poznávačkami, skupinami, přírodninami, obrázky a hlášeními
     * @param int $classId ID třídy k odstranění
     */
    public function deleteClass(int $classId): void
    {
        $class = new ClassObject(false, $classId);
        $class->deleteAsAdmin();
        unset($class);
    }
    
    /**
     * Metoda skrývající obrázek s daným ID z databáze i se všemi jeho hlášeními
     * @param int $pictureId ID obrázku k odstranění
     */
    public function disablePicture(int $pictureId): void
    {
        $picture = new Picture(false, $pictureId);
        $picture->disable();
        $picture->deleteReports();
    }
    
    /**
     * Metoda odstraňující obrázek s daným ID z databáze i se všemi jeho hlášeními
     * @param int $pictureId ID obrázku k odstranění
     */
    public function deletePicture(int $pictureId): void
    {
        $picture = new Picture(false, $pictureId);
        $picture->delete();
    }
    
    /**
     * Metoda odstraňující hlášení s daným ID z databáze
     * @param int $reportId ID hlášení k odstranění
     */
    public function deleteReport(int $reportId): void
    {
        $report = new Report(false, $reportId);
        $report->delete();
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
     * @return TRUE, pokud se vše povedlo, FALSE, pokud se nepodařilo odeslat e-mail
     */
    public function resolveNameChange(int $requestId, bool $classNameChange, bool $approved, string $reason = ""): bool
    {
        $request = ($classNameChange) ? new ClassNameChangeRequest(false, $requestId) : new UserNameChangeRequest(false, $requestId);
        if ($approved)
        {
            $result = $request->approve();
        }
        else
        {
            $result = $request->decline($reason);
        }
        $request->delete();
        return $result;
    }
    
    /**
     * Metoda vkládající HTML e-mailovou zprávu z formuláře v záložce "Poslat e-mail" do připravené šablony a navrací výsledné HTML
     * @param string $rawMessage Obsah hlavního těla e-mailu (může být zformátován pomocí HTML)
     * @param string $rawFooter Obsah patičky e-mailu (může být zformátován pomocí HTML)
     * @return string Kompletní HTML těla e-mailu, které by bylo odesláno
     */
    public function previewEmail(string $rawMessage, string $rawFooter): string
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
    public function sendEmail(string $addressee, string $subject, string $rawMessage, string $rawFooter, string $sender, string $fromAddress): bool
    {
        //Kontrola platnosti e-mailů
        if (!filter_var($addressee, FILTER_VALIDATE_EMAIL)){ throw new AccessDeniedException(AccessDeniedException::REASON_SEND_EMAIL_INVALID_ADDRESSEE_ADDRESS, null, null); }
        if (!filter_var($fromAddress, FILTER_VALIDATE_EMAIL)){ throw new AccessDeniedException(AccessDeniedException::REASON_SEND_EMAIL_INVALID_SENDER_ADDRESS, null, null); }
        
        //Kontrola vyplněnosti ostatních polí
        if (mb_strlen($subject) === 0 || mb_strlen($rawMessage) === 0 || mb_strlen($sender) === 0){ throw new AccessDeniedException(AccessDeniedException::REASON_SEND_EMAIL_EMPTY_FIELDS, null, null); }
        
        $emailBody = $this->previewEmail($rawMessage, $rawFooter);
        $emailSender = new EmailSender();
        return $emailSender->sendMail($addressee, $subject, $emailBody, $fromAddress, $sender);
    }
    
    /**
     * Metoda vykonávající zadané SQL dotazy a navracející jeho výsledky jako HTML
     * @param string $queries SQL dotaz/y, v případě více dotazů musí být ukončeny středníky
     * @return string Zformátovaný výstup dotazu jako HTML určené k zobrazení uživateli
     */
    public function executeSqlQueries(string $queries): string
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

