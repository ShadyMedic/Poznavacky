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
     * @param string $url Zdroj obrázku
     * @param Natural $natural Objekt přírodniny na obrázku
     * @param bool $enabled Stav obrázku (TRUE, pokud je povolen, FALSE, pokud ne)
     */
    public function __construct(int $id, string $url, Natural $natural, bool $enabled)
    {
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
}