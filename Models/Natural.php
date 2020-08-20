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
    private $group;
    private $part;
    
    /**
     * Konstruktor přírodniny nastavující její vlastnosti
     * Pokud je vše specifikováno, nebude potřeba provádět další SQL dotazy
     * Pokud je vyplněno jméno i ID, ale chybí nějaký z dalších argumentů, má jméno přednost před ID
     * Metoda také načítá počet obrázků dané přírodniny.
     * @param int $id ID přírodniny (nepovinné, pokud je specifikováno jméno A poznávačka)
     * @param string $name Název přírodniny (nepovinné, pokud je specifikováno ID)
     * @param Group $group Objekt poznávačky, do které přírodnina patří (nepovinné, pokud je specifikováno ID, pokud není zadáno, bude zjištěno z databáze)
     * @param Part $part Objekt části poznávačky, do které přírodnina patří (pokud není zadáno, bude zjištěno z databáze)
     * @param int $pictureCount Počet obrázků, které jsou od této přírodniny nahrány v databázi (pokud není zadáno, bude zjištěno z databáze)
     * @throws AccessDeniedException V případě, že podle ID nebo jména není v databázi nalezena žádná přírodnina
     * @throws BadMethodCallException V případě, že není specifikován dostatek parametrů
     */
    public function __construct(int $id, string $name = "", Group $group = null, Part $part = null, int $pictureCount = -1)
    {
        if (mb_strlen($name) !== 0 && !empty($id) && !empty($group) && !empty($part) && $pictureCount !== -1)
        {
            //Vše vyplněno --> nastavit
            $this->id = $id;
            $this->name = $name;
            $partId = $part->getId();
            $this->pictureCount = $pictureCount;
        }
        else if (mb_strlen($name) !== 0 && (!empty($group)))
        {
            Db::connect();
            $result = Db::fetchQuery('SELECT prirodniny_id,obrazky,casti_id FROM prirodniny WHERE nazev = ? AND casti_id IN (SELECT casti_id FROM casti WHERE poznavacky_id = ?) LIMIT 1',array($name, $group->getId()));
            if (!$result)
            {
                //Přírodnina nebyla v databázi nalezena
                throw new AccessDeniedException(AccessDeniedException::REASON_NATURAL_NOT_FOUND);
            }
            $id = $result['prirodniny_id'];
            $partId = $result['casti_id'];
            $this->pictureCount = $result['obrazky'];
        }
        else if (!empty($id))
        {
            Db::connect();
            $result = Db::fetchQuery('SELECT nazev,obrazky,casti_id FROM prirodniny WHERE prirodniny_id = ? LIMIT 1',array($id));
            if (!$result)
            {
                //Přírodnina nebyla v databázi nalezena
                throw new AccessDeniedException(AccessDeniedException::REASON_NATURAL_NOT_FOUND);
            }
            $name = $result['nazev'];
            $partId = $result['casti_id'];
            $this->pictureCount = $result['obrazky'];
        }
        else
        {
            throw new BadMethodCallException('Either ID or name and group must be specified.', null, null);
        }
        $this->id = $id;
        $this->name = $name;
        
        //Nastavit nebo zjistit části
        if (!empty($part) && $part->getId() === $partId)
        {
            //ID souhlasí a objekt je poskytnut --> nastavit
            $this->part = $part;
        }
        else
        {
            //Objekt není poskytnut, nebo nesouhlasí ID --> vytvořit
            if (!empty($group))
            {
                //Využít specifikované poznávačky
                $this->part = new Part(false, $partId);
                $this->part->initialize(null, $group, null, null, null);
            }
            else
            {
                $this->part = new Part(false, $partId);
            }
        }
        
        //Nastavení poznávačky (její objekt byl zkonstruován buď předán nebo zkonstruován při tvorbě objektu části)
        $this->group = $this->part->getGroup();
    }
    
    /**
     * Metoda navracející ID této přírodniny
     * @return int ID této přírodniny
     */
    public function getId()
    {
        return $this->id;
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
     * Metoda navracející objekt části, do které je tato přírodnina přiřazena
     * @return Part Část, do které přírodnina spadá
     */
    public function getPart()
    {
        return $this->part;
    }
    
    /**
     * Metoda navracející počet obrázků této přírodniny
     * @return int Počet obrázků této přírodniny uložené v databázi
     */
    public function getPicturesCount()
    {
        return $this->pictureCount;
    }
    
    /**
     * Metoda navracející pole všech obrázků této přírodniny jako objekty
     * Pokud zatím nebyly adresy načteny z databáze, budou načteny.
     * @return Picture[] Pole obrázků této přírodniny z databáze jako objekty
     */
    public function getPictures()
    {
        if (!isset($this->pictures))
        {
            $this->loadPictures();
        }
        return $this->pictures;
    }
    
    /**
     * Metoda navracející náhodný obrázek této příodniny jako objekt
     * Pokud zatím nebyly adresy načteny z databáze, budou načteny.
     * @return Picture Náhodný obrázek této přírodniny
     */
    public function getRandomPicture()
    {
        if (!isset($this->pictures))
        {
            $this->loadPictures();
        }
        return $this->pictures[rand(0, $this->pictureCount - 1)];
    }
    
    /**
     * Metoda načítající z databáze obrázky přírodnin a ukládající je jako vlastnost objektu
     */
    private function loadPictures()
    {
        $this->pictures = array();
        
        Db::connect();
        $result = Db::fetchQuery('SELECT obrazky_id,zdroj,povoleno FROM obrazky WHERE prirodniny_id = ?', array($this->id), true);
        if ($result === false || count($result) === 0)
        {
            //Žádné obrázky nenalezeny
            $this->pictures = array();
        }
        else
        {
            foreach ($result as $pictureData)
            {
                $status = ($pictureData['povoleno'] === 1) ? true : false;
                $this->pictures[] = new Picture($pictureData['obrazky_id'], $pictureData['zdroj'], $this, $status);
            }
        }
    }
    
    /**
     * Metoda přidávající do databáze i do instance třídy nový obrázek této přírodniny
     * @param string $url Ošetřená adresa obrázku
     * @return boolean TRUE, pokud je obrázek přidán úspěšně, FALSE, pokud ne
     */
    public function addPicture(string $url)
    {
        Db::connect();
        $result = Db::executeQuery('INSERT INTO obrazky (prirodniny_id,zdroj,casti_id) VALUES (?,?,?)', array($this->id, $url, $this->part->getId()), true);
        if ($result)
        {
            $this->pictureCount++;
            $this->pictures[] = new Picture($result, $url, $this, true);
            return true;
        }
        return false;
    }
    
    /**
     * Metoda kontrolující, zda je u této přírodniny již nahrán obrázek s danou adresou
     * Pokud zatím nebyly adresy načteny z databáze, budou načteny.
     * @param string $url Adresa obrázku, kterou hledáme
     * @return boolean TRUE, pokud tato přírodnina již má tento obrázek přidaný, FALSE, pokud ne
     */
    public function pictureExists(string $url)
    {
        if (!isset($this->pictures))
        {
            $this->loadPictures();
        }
        
        foreach ($this->pictures as $picture)
        {
            if ($picture->getSrc() === $url)
            {
                return true;
            }
        }
        return false;
    }
}

