<?php
namespace Poznavacky\Models;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Statics\Settings;
use Poznavacky\Models\Statics\UserManager;

/**
 * Třída starající se o kontrolu, zda se má zobrazit poznámky k vydání a získávající jeho obsah
 * @author Jan Štěch
 */
class ChangelogManager
{
    private const GITHUB_API_RELEASES_URL = 'https://api.github.com/repos/ShadyMedic/Poznavacky/releases/';
    private const RELEASE_IDS = array(
        '4.2' => 127441965
        '4.1.2' => 84261860,
        '4.1.1' => 75769088,
        '4.0.1' => 48583462,
        '4.0' => 41734877,
        '3.2' => 22530404,
        '3.1' => 22501973,
        '3.0' => 20363850,
        '2.2' => 17117515,
        '2.1' => 16763591,
        '2.0' => 20325542
    );
    
    private string $title;
    private string $content;
    
    /**
     * Metoda kontrolující, zda si přihlášený uživatel přečetl nejnovější poznámky k vydání
     * @return bool TRUE, pokud byly přihlášenému uživateli zobrazeny nejnovější poznámky k vydání, FALSE, pokud ne
     * @throws AccessDeniedException Pokud není žádný uživatel přihlášen
     */
    public function checkLatestChangelogRead(): bool
    {
        $lastReadChangelog = UserManager::getUser()['lastChangelog'];
        return !($lastReadChangelog < Settings::VERSION);
    }
    
    /**
     * Metoda načítající nadpis a obsah nejnovějších poznámek k vydání z GitHub repozitáře
     */
    private function loadChangelog(): void
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, self::GITHUB_API_RELEASES_URL.self::RELEASE_IDS[Settings::VERSION]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if (!Settings::PRODUCTION_ENVIRONMENT) {
            // fix chyby s SSL certifikátem - pouze pro vývoj stránek
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        }

        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('User-Agent: Poznavacky'));
        
        $response = curl_exec($curl);
        $jsonObject = json_decode($response);
        
        $markdownConvertor = new GithubFlavoredMarkdownConverter();
        
        $this->title = $jsonObject->name;
        $this->content = $markdownConvertor->convertToHtml($jsonObject->body);
    }
    
    /**
     * Metoda navracející nadpis nejnovějších poznámek k vydání
     * Pokud není informace načtena, je stažena z GitHub repozitáře
     * @return string Nadpis poznámek k vydání
     */
    public function getTitle(): string
    {
        if (!isset($this->title)) {
            $this->loadChangelog();
        }
        
        return $this->title;
    }
    
    /**
     * Metoda navracející obsah nejnovějších poznámek k vydání
     * Pokud není informace načtena, je stažena z GitHub repozitáře
     * @return string Obsah poznámek k vydání
     */
    public function getContent(): string
    {
        if (!isset($this->content)) {
            $this->loadChangelog();
        }
        
        return $this->content;
    }
}
