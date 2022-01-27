<?php
namespace Poznavacky\Models;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use \UnexpectedValueException;

/**
 * Třída starající se o získání obsahu souborů s podmínkami služby a zásadami o ochraně soukromí z GitHub repozitáře
 * @author Jan Štěch
 */
class GitHubFileFetcher
{
    private const GITHUB_API_REPOSITORY_URL = 'https://api.github.com/repos/ShadyMedic/Poznavacky/contents/';
    private const TERMS_OF_SERVICE_PATH = 'docs/TERMS_OF_SERVICE.md';
    private const PRIVACY_POLICY_PATH = 'docs/PRIVACY_POLICY.md';
    private const COOKIES_INFO_PATH = 'docs/COOKIES_INFO.md';
    
    private string $termsOfServiceHtml;
    private string $privacyPolicyHtml;
    private string $cookiesInfoHtml;
    
    /**
     * Metoda stahující JSON data o markdown souboru hostovaném na GitHub na specifikovaném URL
     * @param string $url API URL adresa k souboru
     * @return string Obsah markdown souboru převedený do HTML
     * @throws UnexpectedValueException Pokud požadavek na stažení souboru z GitHub repozitáře navrátí neočekávanou
     *     hodnotu
     */
    private function fetchData(string $url): string
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('User-Agent: Poznavacky'));
        
        $response = curl_exec($curl);
        $jsonObject = json_decode($response);
        
        if (!isset($jsonObject->content)) {
            throw new UnexpectedValueException("
                Request to GitHub API returned an unexpected value. 
                Maybe the connection is not working or the resource is not found. 
                Try again later or report this error to the webmaster.
            ");
        }
        
        $content = base64_decode($jsonObject->content);
        
        $markdownConvertor = new GithubFlavoredMarkdownConverter();
        
        return $markdownConvertor->convertToHtml($content);
    }
    
    /**
     * Metoda navracející HTML kód pro zobrazení podmínek služby
     * Pokud tento soubor není načten, tato metoda jej stáhne
     * @return string HTML dokument s podmínkami služby
     */
    public function getTermsOfService(): string
    {
        if (!isset($this->termsOfServiceHtml)) {
            $this->termsOfServiceHtml = $this->fetchData(self::GITHUB_API_REPOSITORY_URL.self::TERMS_OF_SERVICE_PATH);
        }
        
        return $this->termsOfServiceHtml;
    }
    
    /**
     * Metoda navracející HTML kód pro zobrazení zásad ochrany soukromí
     * Pokud tento soubor není načten, tato metoda jej stáhne
     * @return string HTML dokument se zásadami ochrany soukromí
     */
    public function getPrivacyPolicy(): string
    {
        if (!isset($this->privacyPolicyHtml)) {
            $this->privacyPolicyHtml = $this->fetchData(self::GITHUB_API_REPOSITORY_URL.self::PRIVACY_POLICY_PATH);
        }
        
        return $this->privacyPolicyHtml;
    }
    
    /**
     * Metoda navracející HTML kód pro zobrazení podrobných informací o využívání souborů cookies
     * Pokud tento soubor není načten, tato metoda jej stáhne
     * @return string HTML dokument s informacemi o využívání souborů cookies
     */
    public function getCookiesInfo(): string
    {
        if (!isset($this->cookiesInfoHtml)) {
            $this->cookiesInfoHtml = $this->fetchData(self::GITHUB_API_REPOSITORY_URL.self::COOKIES_INFO_PATH);
        }
        
        return $this->cookiesInfoHtml;
    }
}
