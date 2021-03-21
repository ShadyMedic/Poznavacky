<?php
namespace Poznavacky\Models\DatabaseItems;

use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Security\DataValidator;
use \Transliterator;

/** 
 * Abstraktní třída obsahující vlastnosti a metody společné pro třídy, poznávačky i části
 * @author Jan Štěch
 */
abstract class Folder extends DatabaseItem
{
    protected $name;
    protected $url;

    /**
     * Metoda navracející jméno této třídy, poznávačky, nebo části
     * @return string Jméno složky
     * @throws DatabaseException
     */
    public function getName(): string
    {
        $this->loadIfNotLoaded($this->name);
        return $this->name;
    }

    /**
     * Metoda navracející reprezentaci jména této třídy, poznávačky, nebo části pro použití v url
     * @return string URL jméno složky
     * @throws DatabaseException
     */
    public function getUrl(): string
    {
        $this->loadIfNotLoaded($this->url);
        return $this->url;
    }
    
    /**
     * Statická metoda generující název složky pro použití jako argument v URL z jejího názvu
     * Více různých jmen (lišících se pouze nepísmenými a nečíselnými znaky) může vyústit ve stejnou URL
     * @param string $name Název pro převedení na URL
     * @return string URL forma názvu
     */
    public static function generateUrl(string $name): string
    {
        //Převést na malá písmena
        $url = mb_strtolower($name);
        
        //Odstranit diakritiku
        //Kód napsaný podle odpovědi na StackOverflow: https://stackoverflow.com/a/35178027
        $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;', Transliterator::FORWARD);
        $url = $transliterator->transliterate($url);
        
        //Nahradit všechny ostatní povolené znaky pomlčkami/mínusy/spojovníky/whatever "-" is
        $charsToReplace = DataValidator::CLASS_NAME_ALLOWED_CHARS.DataValidator::GROUP_NAME_ALLOWED_CHARS.DataValidator::PART_NAME_ALLOWED_CHARS;
        $charsToReplace = str_replace(mb_str_split(DataValidator::URL_ALLOWED_CHARS), '', $charsToReplace);
        $uniqueCharsToReplace = '';
        while (mb_strlen($charsToReplace) > 0)
        {
            $uniqueCharsToReplace .= mb_substr($charsToReplace, 0, 1);
            $charsToReplace = str_replace(mb_substr($charsToReplace, 0, 1), '', $charsToReplace);
        }
        $url = str_replace(mb_str_split($uniqueCharsToReplace), '-', $url);
        
        //Nahradit násobné "-" za jeden "-"
        do
        {
            $lastLength = mb_strlen($url);
            $url = str_replace('--', '-', $url);
        } while ($lastLength > mb_strlen($url));
        
        //Oříznout pomlčky na začátku i konci
        $url = trim($url, '-');
        
        return $url;
    }
}

