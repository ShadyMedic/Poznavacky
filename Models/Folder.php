<?php
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
     */
    public function getName(): string
    {
        $this->loadIfNotLoaded($this->name);
        return $this->name;
    }
    
    /**
     * Metoda navracející reprezentaci jména této třídy, poznávačky, nebo části pro použití v url
     * @return string URL jméno složky
     */
    public function getUrl(): string
    {
        $this->loadIfNotLoaded($this->url);
        return $this->url;
    }
}
