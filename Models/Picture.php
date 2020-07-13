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
        
        //Odstranit hlášení z databáze
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