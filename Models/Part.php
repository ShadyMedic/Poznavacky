<?php
/** 
 * Třída reprezentující objekt části obsahující přírodniny
 * @author Jan Štěch
 */
class Part extends DatabaseItem
{
    public const TABLE_NAME = 'casti';
    
    public const COLUMN_DICTIONARY = array(
        'id' => 'casti_id',
        'name' => 'nazev',
        'group' => 'poznavacky_id',
        'naturalsCount' => 'prirodniny',
        'picturesCount' => 'obrazky'
    );
    
    protected const NON_PRIMITIVE_PROPERTIES = array(
        'group' => Group::class
    );
    
    protected const DEFAULT_VALUES = array(
        'picturesCount' => 0,
        'naturalsCount' => 0,
        'naturals' => array()
    );
    
    protected const CAN_BE_CREATED = true;
    protected const CAN_BE_UPDATED = true;
    
    protected $name;
    protected $group;
    protected $naturalsCount;
    protected $picturesCount;
    
    protected $naturals;
    
    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * Při nastavení některého z argumentů na undefined, je hodnota dané vlastnosti také nastavena na undefined
     * Při nastavení některého z argumentů na null, není hodnota dané vlastnosti nijak pozměněna
     * @param string|undefined|null $name Název části
     * @param Group|undefined|null $group Odkaz na objekt poznávačky, do níž tato část patří
     * @param Natural[]|undefined|null $naturals Pole přírodnin, které patří do této části poznávačky, jako objekty
     * @param int|undefined|null $naturalsCount Počet přírodnin v této části poznávačky (při vyplnění parametru $naturals je ignorováno a je použita délka poskytnutého pole)
     * @param int|undefined|null $picturesCount Počet obrázků v této části poznávačky
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($name = null, $group = null, $naturals = null, $naturalsCount = null, $picturesCount = null)
    {
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($name === null){ $name = $this->name; }
        if ($group === null){ $group = $this->group; }
        if ($naturals === null)
        {
            $naturals = $this->naturals;
            if ($naturalsCount === null){ $naturalsCount = $this->naturalsCount; }
        }
        else { $naturalsCount = count($naturals); }
        if ($picturesCount === null){ $picturesCount = $this->picturesCount; }
        
        $this->name = $name;
        $this->group = $group;
        $this->naturals = $naturals;
        $this->naturalsCount = $naturalsCount;
        $this->picturesCount = $picturesCount;
    }
        
    /**
     * Metoda navracející jméno této části
     * @return string Jméno části
     */
    public function getName()
    {
        $this->loadIfNotLoaded($this->name);
        return $this->name;
    }
    
    /**
     * Metoda navracející objekt poznávačky, do které tato část patří
     * @return Group Poznávačka do které patří část
     */
    public function getGroup()
    {
        $this->loadIfNotLoaded($this->group);
        return $this->group;
    }
    
    /**
     * Metoda navracející počet obrázků v této části
     * @return int počet obrázků v části
     */
    public function getPicturesCount()
    {
        $this->loadIfNotLoaded($this->picturesCount);
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
        if (!$this->isDefined($this->naturals))
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
        $this->loadIfNotLoaded($this->naturalsCount);
        return $this->naturalsCount;
    }
    
    /**
     * Metoda navracející objekty přírodnin patřících do této poznávačky
     * Pokud zatím nebyly přírodniny načteny, budou načteny z databáze
     * @return array Pole přírodnin v této části jako objekty
     */
    public function getNaturals()
    {
        if (!$this->isDefined($this->naturals))
        {
            $this->loadNaturals();
        }
        return $this->naturals;
    }
    
    /**
     * Metoda načítající přírodniny patřící do této části a ukládající je jako vlastnost
     */
    public function loadNaturals()
    {
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        $result = Db::fetchQuery('SELECT '.Natural::COLUMN_DICTIONARY['id'].','.Natural::COLUMN_DICTIONARY['name'].','.Natural::COLUMN_DICTIONARY['picturesCount'].' FROM '.Natural::TABLE_NAME.' WHERE '.Natural::COLUMN_DICTIONARY['part'].' = ?', array($this->id), true);
        if ($result === false || count($result) === 0)
        {
            //Žádné části přírodniny
            $this->naturals = array();
        }
        else
        {
            $this->naturals = array();
            foreach ($result as $naturalData)
            {
                $natural = new Natural(false, $naturalData[Natural::COLUMN_DICTIONARY['id']]);
                $natural->initialize($naturalData[Natural::COLUMN_DICTIONARY['name']], null, $naturalData[Natural::COLUMN_DICTIONARY['picturesCount']], null, $this->getGroup(), $this);
                $this->naturals[] = $natural;
            }
        }
    }
    
    /**
     * Metoda odstraňující tuto část z databáze
     * @return boolean TRUE, pokud je část úspěšně odstraněna z databáze
     * {@inheritDoc}
     * @see DatabaseItem::delete()
     */
    public function delete()
    {
        $this->loadIfNotLoaded($this->id);
        
        Db::connect();
        Db::executeQuery('DELETE FROM '.self::TABLE_NAME.' WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1;', array($this->id));
        $this->id = new undefined();
        $this->savedInDb = false;
        return true;
    }
}