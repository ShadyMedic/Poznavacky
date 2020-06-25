<?php
/** 
 * Třída reprezentující hlášení obrázku
 * @author Jan Štěch
 */
class Report
{
    const ADMIN_REQUIRING_REASONS = array(self::REASON_COPYRIGHT, self::REASON_OTHER_ADMIN);
    const LONG_LOADING_AVAILABLE_DELAYS = array('>2 s', '>5 s', '>10 s', '>20 s');
    const INCORRECT_NATURAL_DEFAULT_INFO = 'Nezadáno';
    
    const REASON_NOT_DISPLAYING = 'Obrázek se nezobrazuje správně';
    const REASON_LONG_LOADING = 'Obrázek se načítá příliš dlouho';
    const REASON_INCORRECT_NATURAL = 'Obrázek zobrazuje nesprávnou přírodninu';
    const REASON_CONTAINS_NAME = 'Obrázek obsahuje název přírodniny';
    const REASON_BAD_RESOLUTION = 'Obrázek má příliš špatné rozlišení';
    const REASON_COPYRIGHT = 'Obrázek porušuje autorská práva';
    const REASON_OTHER = 'Jiný důvod (řeší správce třídy)';
    const REASON_OTHER_ADMIN = 'Jiný důvod (řeší správce služby)';
    
    private $id;
    private $picture;
    private $reason;
    private $additionalInformation;
    private $reportersCount;
    
    /**
     * Konstruktor hlášení nastavující všechny jeho vlastnosti
     * @param int $id ID hlášení (nepovinné, v případě zadání 0 nebude svázáno se záznamem v databázi dokud není zavolána metoda load nebo save)
     * @param Picture $picture Odkaz na objekt obrázku, kterého se toto hlášení týká (nepovinné; pokud je vyplněno ID, může být načteno z databáze pomocí metody load)
     * @param string $reason Důvod hlášení (musí být hodnota jedne z konstant této třídy začánající na REASON_; nepovinné; pokud je vyplněno ID, může být načteno z databáze pomocí metody load)
     * @param string $additionalInformation Další informace, které může uživatel u určitých hlášení specifikovat (nepovinné; pokud je vyplněno ID, může být načteno z databáze pomocí metody load)
     * @param int $reportersCount Počet uživatelů, kteří tento obrázek z tohoto důvodu nahlásili (nepovinné; pokud je vyplněno ID, může být načteno z databáze pomocí metody load; pro nespecifikaci vyplňte -1 nebo nic)
     */
    public function __construct(int $id = 0, Picture $picture = null, string $reason = "", string $additionalInformation = "", int $reportersCount = -1)
    {
        $this->id = $id;
        $this->picture = $picture;
        $this->reason = $reason;
        $this->additionalInformation = $additionalInformation;
        $this->reportersCount = $reportersCount;
    }
    
    /**
     * Metoda navracející ID nahlášeného obrázku
     * @return int ID obrázku
     */
    public function getPictureId()
    {
        return $this->picture->getId();
    }
    
    /**
     * Metoda navracející URL nahlášeného obrázku
     * @return string Zdroj obrázku
     */
    public function getUrl()
    {
        return $this->picture->getSrc();
    }
    
    /**
     * Metoda navracející řetězec se zařazením obrázku ve formátu <Název třídy> / <Název poznávačky> / <Název části> / <Název přírodniny>
     * @return string Řetězec obsahující cestu k obrázku
     */
    public function getPicturePath()
    {
        $natural = $this->picture->getNatural();
        $part = $natural->getPart();
        $group = $part->getGroup();
        $class = $group->getClass();
        return $class->getName().' / '.$group->getName().' / '.$part->getName();
    }
    
    /**
     * Metoda navracející název přírodniny, ke které byl nahlášený obrázek nahrán
     * @return string Název přírodniny na obrázku
     */
    public function getNaturalName()
    {
        $natural = $this->picture->getNatural();
        return $natural->getName();
    }
    
    /**
     * Metoda navracející důvod hlášení
     * @return string Důvod hlášení (měl by být jednou z konstant této třídy)
     */
    public function getReason()
    {
        return $this->reason;
    }
    
    /**
     * Metoda navracející další informace o hlášení
     * @return string Další informace o hlášení (pokud žádné nebyly poskytnuty, tak prázdný řetězec)
     */
    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }
    
    /**
     * Metoda navracející počet hlášení stejného typu
     * @return int Počet hlášení
     */
    public function getReportersCount()
    {
        return $this->reportersCount;
    }
    
    /**
     * Metoda zvyšující počet hlášení tohoto typu o 1
     */
    public function increaseReportersCount()
    {
        $this->reportersCount++;
}
    
    /**
     * Metoda načítající z databáze ID tohoto hlášení a číslo, kolikrát bylo hlášení tohoto typu odesláno (podle obrázku, důvodu a dalších informací)
     * Pokud není takové hlášení v databázi nalezeno, je vlastnost $id ponechána nenastavená a vlastnost $reportersCount nastavena na 1
     * Pokud je již ID tohoto hlášení nastaveno, jsou naopak podle něj načteny ostatní vlastnosti instance
     */
    public function load()
    {
        Db::connect();
        if (!empty($this->id))
        {
            //Je zadáno ID - načíst podle něj ostatní informace
            $reportInfo = Db::fetchQuery('
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
            WHERE hlaseni.hlaseni_id = ?
            LIMIT 1;
            ', array($this->id), false);
            
            $class = new ClassObject($reportInfo['tridy_id'], $reportInfo['tridy_nazev']);
            $group = new Group($reportInfo['poznavacky_id'], $reportInfo['poznavacky_nazev'], $class, $reportInfo['poznavacky_casti']);
            $part = new Part($reportInfo['casti_id'], $reportInfo['casti_nazev'], $group, $reportInfo['casti_prirodniny'], $reportInfo['casti_obrazky']);
            $natural = new Natural($reportInfo['prirodniny_id'], $reportInfo['prirodniny_nazev'], $group, $part, $reportInfo['prirodniny_obrazky']);
            $picture = new Picture($reportInfo['obrazky_id'], $reportInfo['obrazky_zdroj'], $natural, $reportInfo['obrazky_povoleno']);
            
            $this->picture = $picture;
            $this->reason = $reportInfo['hlaseni_duvod'];
            $this->additionalInformation = $reportInfo['hlaseni_dalsi_informace'];
            $this->reportersCount = $reportInfo['hlaseni_pocet'];
        }
        else
        {
            //Není zadáno ID - načíst ID podle ostatních informací
            $dbResult = Db::fetchQuery('SELECT hlaseni_id, pocet FROM hlaseni WHERE obrazky_id = ? AND duvod = ? AND dalsi_informace = ? LIMIT 1;', array($this->picture->getId(), $this->reason, $this->additionalInformation), false);
            if (!$dbResult)
            {
            //Takové hlášení zatím v databázi neexistuje
            $this->reportersCount = 0;
        }
        else
        {
            //Hlášení nalezeno
            $this->id = $dbResult['hlaseni_id'];
                $this->reportersCount = $dbResult['pocet'];
            }
        }
    }
    
    /**
     * Metoda ukládající data tohoto hlášení do databáze
     * Pokud je nastavena vlastnost $id, je v databázi hlášení se stejným ID nahrazeno současnými informacemi
     * V opačném případě je do databáze vloženo nové hlášení a vlastnost $id je vyplněna podle ID posledního vloženého řádku do databáze
     * Před zavoláním této metody musí být proveden pokus, zda takovéto hlášení již v databázi neexistuje pomocí metody Report::load()
     * @throws BadMethodCallException Pokud nebyl před zavoláním této metody proveden test, zda takové hlášení již v databázi existuje pomocí metody Report::load()
     */
    public function save()
    {
        if (!empty($this->id))
        {
            //Aktualizace existujícího hlášení
            Db::executeQuery('UPDATE hlaseni SET obrazky_id = ?,duvod = ?,dalsi_informace = ?,pocet = ? WHERE hlaseni_id = ?;', array($this->picture->getId(), $this->reason, $this->additionalInformation, $this->reportersCount, $this->id));
        }
        else if (empty($this->id) && $this->reportersCount !== -1)
        {
            //Tvorba nového hlášení
            $this->id = Db::executeQuery('INSERT INTO hlaseni (obrazky_id,duvod,dalsi_informace,pocet) VALUES (?,?,?,?);', array($this->picture->getId(), $this->reason, $this->additionalInformation, $this->reportersCount), true);
        }
        else
        {
            //Chyba: nejdříve musí být zjištěno, zda již hlášení tohoto typu v databázi neexistuje
            throw new BadMethodCallException('You must call Report::load() before trying to save a report');
        }
    }
}