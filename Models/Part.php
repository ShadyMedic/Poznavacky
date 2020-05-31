<?php
/** 
 * Třída reprezentující objekt části obsahující přírodniny
 * @author Jan Štěch
 */
class Part
{
    private $id;
    private $name;
    private $group;
    private $picturesCount;
    private $naturalsCount;
    private $naturals;
    
    /**
     * Konstruktor části nastavující jeji vlastnosti.
     * Pokud je vše specifikováno, nebude potřeba provádět další SQL dotazy
     * Pokud je vyplněno jméno i ID, ale chybí nějaký z dalších argumentů, má jméno přednost před ID
     * @param int $id ID části (nepovinné, pokud je specifikováno jméno)
     * @param string $name Název části (nepovinné, pokud je specifikováno ID)
     * @param Group $group Objekt poznávačky, do které tato třída patří (pokud není zadáno, bude zjištěno z databáze)
     * @param int $naturalsCount Počet přírodnin, které tato část obsahuje (nepovinné, v případě nevyplnění bude zjištěno z databáze, pro nevyplnění zadejte -1 nebo nic)
     * @param int $picturesCount Počet obrázků, které tato část obsahuje (nepovinné, v případě nevyplnění bude zjištěno z databáze, pro nevyplnění zadejte -1 nebo nic)
     * @throws AccessDeniedException V případě, že podle ID nebo jména není v databázi nalezena žádná část
     * @throws BadMethodCallException V případě, že není specifikován dostatek parametrů
     */
    public function __construct(int $id, string $name = "", Group $group = null, int $naturalsCount = -1, int $picturesCount = -1)
    {
        if (mb_strlen($name) !== 0 && !empty($id) && !empty($group) && $naturalsCount !== -1 && $picturesCount !== -1)
        {
            //Vše je vyplněno --> nastavit
            $this->id = $id;
            $this->name = $name;
            $groupId = $group->getId();
            $this->naturalsCount = $naturalsCount;
            $this->picturesCount = $picturesCount;
        }
        else if (mb_strlen($name) !== 0)
        {
            Db::connect();
            $result = Db::fetchQuery('SELECT casti_id,prirodniny,obrazky,poznavacky_id FROM casti WHERE nazev = ? LIMIT 1',array($name));
            if (!$result)
            {
                //Část nebyla v databázi nalezena
                throw new AccessDeniedException(AccessDeniedException::REASON_PART_NOT_FOUND);
            }
            $id = $result['casti_id'];
            $this->naturalsCount = $result['prirodniny'];
            $this->picturesCount = $result['obrazky'];
            $groupId = $result['poznavacky_id'];
        }
        else if (!empty($id))
        {
            Db::connect();
            $result = Db::fetchQuery('SELECT nazev,prirodniny,obrazky,poznavacky_id FROM casti WHERE casti_id = ? LIMIT 1',array($id));
            if (!$result)
            {
                //Část nebyla v databázi nalezena
                throw new AccessDeniedException(AccessDeniedException::REASON_PART_NOT_FOUND);
            }
            $name = $result['nazev'];
            $this->naturalsCount = $result['prirodniny'];
            $this->picturesCount = $result['obrazky'];
            $groupId = $result['poznavacky_id'];
        }
        else
        {
            throw new BadMethodCallException('Either ID or name and group must be specified.', null, null);
        }
        
        $this->id = $id;
        $this->name = $name;
        
        //Nastavit nebo zjistit poznávačku
        if (!empty($group) && $group->getId() === $groupId)
        {
            //ID souhlasí a objekt je poskytnut --> nastavit
            $this->group = $group;
        }
        else
        {
            //Objekt není poskytnut, nebo nesouhlasí ID --> vytvořit
            $this->group = new Group($groupId);
        }
    }
    
    /**
     * Metoda navracející ID této části
     * @return int ID části
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Metoda navracející jméno této části
     * @return string Jméno části
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Metoda navracející objekt poznávačky, do které tato část patří
     * @return Group Poznávačka do které patří část
     */
    public function getGroup()
    {
        return $this->group;
    }
    
    /**
     * Metoda navracející počet obrázků v této části
     * @return int počet obrázků v části
     */
    public function getPicturesCount()
    {
        return $this->picturesCount;
    }
    
    /**
     * Metoda navracející objekt náhodně vybraného obrázku náhodné přírodniny patřící do této části
     * Všechny přírodniny mají stejnou šanci, že jejich obrázek bude vybrán
     * Počet obrázků u jednotlivých přírodniny nemá na výběr vliv
     * Pokud nejsou při volání této funkce načteny přírodniny této části, budou načteny
     * @param int $count Požadovaný počet náhodných obrázků (není zajištěna absence duplikátů)
     */
    public function getRandomPictures(int $count)
    {
        if (!isset($this->naturals))
        {
            $this->loadNaturals();
        }
        
        $result = array();
        
        for ($i = 0; $i < $count; $i++)
        {
            $randomNaturalNum = rand(0, $this->naturalsCount - 1);
            $result[] = $this->naturals[$randomNaturalNum]->getRandomPicture();
        }
        
        return $result;
    }
    
    /**
     * Metoda navracející počet přírodnin patřících do této části
     * @return int Počet přírodnin v části
     */
    public function getNaturalsCount()
    {
        return $this->naturalsCount;
    }
    
    /**
     * Metoda navracející objekty přírodnin patřících do této poznávačky
     * Pokud zatím nebyly přírodniny načteny, budou načteny z databáze
     * @return array Pole přírodnin v této části jako objekty
     */
    public function getNaturals()
    {
        if (!isset($this->naturals))
        {
            $this->loadNaturals();
        }
        return $this->naturals;
    }
    
    /**
     * Metoda načítající přírodniny patřící do této části a ukládající je jako vlastnost
     */
    private function loadNaturals()
    {
        $this->naturals = array();
        
        Db::connect();
        $result = Db::fetchQuery('SELECT prirodniny_id,nazev,obrazky FROM prirodniny WHERE casti_id = ?', array($this->id), true);
        foreach ($result as $naturalData)
        {
            $this->naturals[] = new Natural($naturalData['prirodniny_id'], $naturalData['nazev'], $this->getGroup(), $this, $naturalData['obrazky']);
        }
    }
}