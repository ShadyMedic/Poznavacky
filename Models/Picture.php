<?php
/** 
 * Třída reprezentující objekt obrázku
 * @author Jan Štěch
 */
class Picture
{
    private $id;
    private $src;
    private $natural;
    private $enabled;
    private $reports;
    
    /**
     * Konstruktor obrázku nastavující jeho vlastnosti
     * @param int $id ID obrázku
     * @param string $url Zdroj obrázku (nepovinné, pokud je zadáno ID)
     * @param Natural $natural Objekt přírodniny na obrázku (nepovinné, pokud je zadáno ID)
     * @param bool $enabled Stav obrázku (TRUE, pokud je povolen, FALSE, pokud ne; nepovinné, pokud je zadáno ID)
     */
    public function __construct(int $id, string $url = "", Natural $natural = null, bool $enabled = null)
    {
        $this->id = $id;
        $this->src = $url;
        $this->natural = $natural;
        $this->enabled = $enabled;
        
        if (mb_strlen($url) !== 0 && !empty($id) && !empty($natural) && isset($enabled))
        {
            //Vše je vyplněno --> nic nezjišťovat
        }
        else if (!empty($id))
        {
            //Je vyplněno ID, ale něco z dalších informací chybí --> načíst z databáze
            Db::connect();
            $result = Db::fetchQuery('SELECT prirodniny_id,zdroj,povoleno FROM obrazky WHERE obrazky_id = ? LIMIT 1',array($id));
            if (!$result)
            {
                //Obrázek nebyl v databázi nalezena
                throw new AccessDeniedException(AccessDeniedException::REASON_PICTURE_NOT_FOUND);
            }
            $url = $result['zdroj'];
            $natural = new Natural($result['prirodniny_id']);
            $enabled = ($result['povoleno'] == 1) ? true : false;
        }
        else
        {
            throw new BadMethodCallException('ID of the picture must be specified.', null, null);
        }
        
        $this->id = $id;
        $this->src = $url;
        $this->natural = $natural;
        $this->enabled = $enabled;
    }
    
    /**
     * Metoda navracející ID tohoto obrázku
     * @return int ID obrázku
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Metoda navracející URL adresu toho obrázku
     * @return string Zdroj (URL) obrázku
     */
    public function getSrc()
    {
        return $this->src;
    }
    
    /**
     * Metoda navracející objekt přírodniny, kterou zachycuje tento obrázek
     * @return Natural Přírodnina na obrázku
     */
    public function getNatural()
    {
        return $this->natural;
    }
    
    /**
     * Metoda navracející stav obrázku
     * @return bool TRUE, je-li obrázek povolený, FALSE, pokud je skrytý
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
    
    /**
     * Metoda odstraňující z databáze všechna hlášení vztahující se k tomuto obrázku
     * Vlastnost pole uchovávající hlášení tohoto obrázku, které je uložené jako vlastnost je nahrazeno prázdným polem
     * @return boolean TRUE, pokud jsou hlášení úspěšně odstraněna
     */
    public function deleteReports()
    {
        Db::connect();
        Db::executeQuery('DELETE FROM hlaseni WHERE obrazky_id = ?', array($this->id));
        $this->reports = array();
        return true;
    }
    
    /**
     * Metoda navracející pole hlášení tohoto obrázku
     * Pokud hlášení zatím nebyla načtena z databáze, budou před navrácením načtena
     * @return Report[] Pole hlášení tohoto obrázku jako objekty
     */
    public function getReports()
    {
        $this->checkReportsLoaded();
        return $this->reports;
    }
    
    private function checkReportsLoaded()
    {
        if (!isset($this->reports))
        {
            $this->loadReports();
        }
    }
    
    /**
     * Metoda načítající hlášení tohoto obrázku z databáze a ukládající je do vlastnosti této instance jako objekty
     */
    private function loadReports()
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT hlaseni_id,duvod,dalsi_informace,pocet FROM hlaseni WHERE obrazky_id = ?', array($this->id), true);
        
        if (count($result) === 0)
        {
            //Žádná hlášení tohoto obrázku
            $this->reports = array();
            return;
        }
        
        foreach ($result as $reportInfo)
        {
            //Konstrukce nových objektů hlášení a jejich ukládání do pole
            $this->reports[] = new Report($reportInfo['hlaseni_id'], $this, $reportInfo['duvod'], $reportInfo['dalsi_informace'], $reportInfo['pocet']);
        }
    }
    
    /**
     * Metoda skrývající tento obrázek v databázi
     * Tato metoda může být použita pouze v případě, že právě přihlášený uživatel je systémový administrátor
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem
     * @return boolean TRUE, pokud je obrázek úspěšně skryt v databázi
     */
    public function disable()
    {
        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        if (!AccessChecker::checkSystemAdmin())
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
        
        //Kontrola dat OK
        
        //Vypnout obrázek v databázi
        Db::connect();
        Db::executeQuery('UPDATE obrazky SET povoleno = 0 WHERE obrazky_id = ? LIMIT 1;', array($this->id));
        
        //Přenastavit vlastnost této instance
        $this->enabled = false;
    }
    
    /**
     * Metoda odstraňující tento obrázek z databáze
     * Vlastnosti této instance jsou vynulovány
     * Insance, na níž je vykonána tato metoda by měla být okamžitě zničena pomocí unset()
     * Tato metoda může být použita pouze v případě, že právě přihlášený uživatel je systémový administrátor
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem
     * @return boolean TRUE, pokud je obrázek úspěšně odstraněn z databáze
     */
    public function delete()
    {
        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        if (!AccessChecker::checkSystemAdmin())
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
        
        //Kontrola dat OK
        
        //Odstranit obrázek z databáze
        Db::connect();
        Db::executeQuery('DELETE FROM obrazky WHERE obrazky_id = ? LIMIT 1;', array($this->id));
        
        //Vymazat data z této instance hlášení
        $this->id = null;
        $this->src = null;
        $this->natural = null;
        $this->enabled = null;
        
        return true;
    }
}