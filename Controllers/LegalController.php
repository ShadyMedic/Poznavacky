<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\GitHubFileFetcher;
use Poznavacky\Models\Logger;
use \UnexpectedValueException;

/**
 * Kontroler stránky s právními informacemi
 * @author Jan Štěch
 */
class LegalController extends SynchronousController
{
    
    /**
     * Metoda nastavující hlavičku požadavku, titulek stránky a zobrazovaný pohled stránky
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see SynchronousController::process()
     */
    function process(array $parameters): void
    {
        (new Logger(true))->info('Přístup na stránku legal z IP adresy {ip}', array('ip' => $_SERVER['REMOTE_ADDR']));
        
        self::$pageHeader['title'] = 'Právní informace';
        self::$pageHeader['description'] = 'Zde si můžete přečíst, co od vás za používání naší služby vyžadujeme, jaké jsou naše povinnosti vůči vám, jak nakládáme s vašemi údaji, jaké údaje přesně ukládáme a z jakého důvodu a také jaké soubory cookie ukládáme na vaše zařízení a proč tak činíme.';
        self::$pageHeader['keywords'] = 'podmínky, pravidla, zákon, dokument, právo, práva, povinnosti, soukromí, zásady, údaje, data, cookies, soubor, informace';
        self::$pageHeader['cssFiles'] = array('css/css.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/legal.js');
        self::$pageHeader['bodyId'] = 'legal';
        
        $githubFetcher = new GitHubFileFetcher();
        try {
            self::$data['staticContent'][0] = $githubFetcher->getTermsOfService();
        } catch (UnexpectedValueException $e) {
            self::$data['staticContent'][0] = $e->getMessage();
        }
        try {
            self::$data['staticContent'][1] = $githubFetcher->getPrivacyPolicy();
        } catch (UnexpectedValueException $e) {
            self::$data['staticContent'][1] = $e->getMessage();
        }
        try {
            self::$data['staticContent'][2] = $githubFetcher->getCookiesInfo();
        } catch (UnexpectedValueException $e) {
            self::$data['staticContent'][2] = $e->getMessage();
        }
    }
}

