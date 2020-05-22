<?php
/** 
 * Třída reprezentující objekt přírodniny
 * @author Jan Štěch
 */
class Natural
{
    private $id;
    private $name;
    private $pictureCount;
    private $pictures;
    
    /**
     * Konstruktor přírodniny nastavující její ID a název. Pokud je specifikováno ID i název, má název přednost.
     * Metoda také načítá počet obrázků dané přírodniny.
     * @param int $id ID přírodniny (nepovinné, pokud je specifikováno jméno)
     * @param string $name Název přírodniny (nepovinné, pokud je specifikováno ID)
     */
    public function __construct(int $id, string $name = "")
    {
        if (mb_strlen($name) !== 0)
        {
            Db::connect();
            $result = Db::fetchQuery('SELECT prirodniny_id,obrazky FROM prirodniny WHERE nazev = ? LIMIT 1',array($name));
            $id = $result['prirodniny_id'];
            $this->pictureCount = $result['obrazky'];
        }
        else if (!empty($id))
        {
            Db::connect();
            $result = Db::fetchQuery('SELECT nazev,obrazky FROM prirodniny WHERE prirodniny_id = ? LIMIT 1',array($id));
            $name = $result['nazev'];
            $this->pictureCount = $result['obrazky'];
        }
        else
        {
            throw new BadMethodCallException('At least one of the arguments must be specified.', null, null);
        }
        $this->id = $id;
        $this->name = $name;
    }
    
    /**
     * Metoda navracející jméno této přírodniny
     * @return string Jméno přírodniny
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Metoda navracející počet obrázků této přírodniny
     * @return int Počet obrázků této přírodniny uložené v databázi
     */
    public function getPictureCount()
    {
        return $this->pictureCount;
    }
    
    /**
     * Metoda navracející pole všech obrázků této přírodniny jako objekty
     * Pokud zatím nebyly adresy načteny z databáze, budou načteny.
     * @return array Pole obrázků této přírodniny z databáze jako objekty
     */
    public function getPictures()
    {
        if (!isset($this->pictures))
        {
            $this->loadPictures();
        }
        return $this->pictures;
    }
    
    private function loadPictures()
    {
        $this->pictures = array();
        
        Db::connect();
        $result = Db::fetchQuery('SELECT obrazky_id,zdroj,povoleno FROM obrazky WHERE prirodniny_id = ?', array($this->id), true);
        foreach ($result as $pictureData)
        {
            $status = ($pictureData['povoleno'] === 1) ? true : false;
            $this->pictures[] = new Picture($pictureData['obrazky_id'], $pictureData['zdroj'], $this, $status);
        }
    }
}

