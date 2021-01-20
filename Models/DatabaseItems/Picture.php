<?php
namespace Poznavacky\Models\DatabaseItems;

use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Processors\PictureAdder;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\undefined;

/** 
 * Třída reprezentující objekt obrázku
 * @author Jan Štěch
 */
class Picture extends DatabaseItem
{
    public const TABLE_NAME = 'obrazky';
    
    public const COLUMN_DICTIONARY = array(
        'id' => 'obrazky_id',
        'src' => 'zdroj',
        'natural' => 'prirodniny_id',
        'enabled' => 'povoleno'
    );
    
    protected const NON_PRIMITIVE_PROPERTIES = array(
        'natural' => Natural::class
    );
    
    protected const DEFAULT_VALUES = array(
        'enabled' => true
    );
    
    protected const CAN_BE_CREATED = true;
    protected const CAN_BE_UPDATED = true;
    
    protected $src;
    protected $natural;
    protected $enabled;
    
    protected $reports;
    
    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * Při nastavení některého z argumentů na undefined, je hodnota dané vlastnosti také nastavena na undefined
     * Při nastavení některého z argumentů na null, není hodnota dané vlastnosti nijak pozměněna
     * @param string|undefined|null $src Adresa, pod kterou lze obrázek najít
     * @param Natural|undefined|null $natural Odkaz na objekt přírodniny, kterou tento obrázek zobrazuje
     * @param bool|undefined|null $enabled TRUE, pokud je obrázek povolen, FALSE, pokud je skryt
     * @param Report[]|undefined|null $reports Pole hlášení tohoto obrázku, jako objekty
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($src = null, $natural = null, $enabled = null, $reports = null): void
    {
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($src === null){ $src = $this->src; }
        if ($natural === null){ $natural = $this->natural; }
        if ($enabled === null){ $enabled = $this->enabled; }
        if ($reports === null){ $reports = $this->reports; }
        
        $this->src = $src;
        $this->natural = $natural;
        $this->enabled = $enabled;
        $this->reports = $reports;
    }
    
    /**
     * Metoda navracející URL adresu toho obrázku
     * @return string Zdroj (URL) obrázku
     */
    public function getSrc(): string
    {
        $this->loadIfNotLoaded($this->src);
        return $this->src;
    }
    
    /**
     * Metoda navracející objekt přírodniny, kterou zachycuje tento obrázek
     * @return Natural Přírodnina na obrázku
     */
    public function getNatural(): Natural
    {
        $this->loadIfNotLoaded($this->natural);
        return $this->natural;
    }
    
    /**
     * Metoda navracející stav obrázku
     * @return bool TRUE, je-li obrázek povolený, FALSE, pokud je skrytý
     */
    public function isEnabled(): bool
    {
        $this->loadIfNotLoaded($this->enabled);
        return $this->enabled;
    }
    
    /**
     * Metoda upravující přírodninu a adresu tohoto obrázku z rozhodnutí administrátora nebo správce třídy
     * Údaje v databázi nejsou aktualizovány - pro potvrzení změn je nutné zavolat metodu Picture::save()
     * @param Natural $newNatural Objekt reprezentující nově zvolenou přírodninu
     * @param string $newUrl Nová adresa k obrázku
     * @param Group $group Objekt reprezentující poznávačku, do které musí nová přírodnina patřit (pro kontrolu)
     * @throws AccessDeniedException Pokud jsou zadaná data neplatná
     * @return boolean TRUE, pokud jsou údaje tohoto obrázku úspěšně aktualizovány
     */
    public function updatePicture(Natural $newNatural, string $newUrl, Group $group): bool
    {
        //Kontrola, zda daná nová URL adresa vede na obrázek a zda je nová přírodnina součástí té samé poznávačky, jako ta stará
        $checker = new PictureAdder($group);
        $checker->checkData($newNatural->getName(), $newUrl);  //Pokud nejsou data v pořádku, nastane výjimka a kód nepokračuje
        
        //Kontrola dat OK
        
        //Aktualizovat údaje ve vlastnostech této instance
        $this->natural = $newNatural;
        $this->src = $newUrl;
        
        return true;
    }
    
    /**
     * Metoda navracející pole hlášení tohoto obrázku
     * Pokud hlášení zatím nebyla načtena z databáze, budou před navrácením načtena
     * @return Report[] Pole hlášení tohoto obrázku jako objekty
     */
    public function getReports(): array
    {
        if (!$this->isDefined($this->reports)){ $this->loadReports(); }
        return $this->reports;
    }
    
    /**
     * Metoda načítající hlášení tohoto obrázku z databáze a ukládající je do vlastnosti této instance jako objekty
     */
    public function loadReports(): void
    {
        $result = Db::fetchQuery('SELECT '.Report::COLUMN_DICTIONARY['id'].','.Report::COLUMN_DICTIONARY['reason'].','.Report::COLUMN_DICTIONARY['additionalInformation'].','.Report::COLUMN_DICTIONARY['reportersCount'].' FROM '.Report::TABLE_NAME.' WHERE '.Report::COLUMN_DICTIONARY['picture'].' = ?', array($this->id), true);
        
        if (count($result) === 0)
        {
            //Žádná hlášení tohoto obrázku
            $this->reports = array();
            return;
        }
        
        foreach ($result as $reportInfo)
        {
            //Konstrukce nových objektů hlášení a jejich ukládání do pole
            $report = new Report(false, $reportInfo[Report::COLUMN_DICTIONARY['id']]);
            $report->initialize($this, $reportInfo[Report::COLUMN_DICTIONARY['reason']], $reportInfo[Report::COLUMN_DICTIONARY['additionalInformation']], $reportInfo[Report::COLUMN_DICTIONARY['reportersCount']]);
            $this->reports[] = $report;
        }
    }
}

