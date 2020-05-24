<?php
/** 
 * Třída reprezentující objekt obrázku
 * @author Jan Štěch
 */
class Picture implements ArrayAccess
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
     * Metoda pro zjišťování existence některé vlastnosti obrázku
     * {@inheritDoc}
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return (isset($this->$offset));
    }
    
    /**
     * Metoda pro získání hodnoty nějaké z vlastností obrázku
     * {@inheritDoc}
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }
    
    /**
     * Metoda pro nastavení hodnoty nějaké z vlastností obrázku
     * Nelze ji použít pro nastavení jakékoliv vlastnosti (všechny jsou read-only)
     * {@inheritDoc}
     * @see ArrayAccess::offsetSet()
     * @throws BadMethodCallException Při zavolání metody
     */
    public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException('It isn\'t allowed to edit picture\'s properities.');
    }
    
    /**
     * Metoda pro odebrání hodnoty nějaké z vlastností obrázku
     * Nelze použít pro odebrání jakékoliv vlastnosti
     * {@inheritDoc}
     * @see ArrayAccess::offsetUnset()
     * @throws BadMethodCallException Při zavolání metody
     */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException('It isn\'t allowed to remove picture\'s properities.');
    }
}