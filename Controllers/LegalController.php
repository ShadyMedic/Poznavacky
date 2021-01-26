<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\GitHubFileFetcher;
use \UnexpectedValueException;

/**
 * Kontroler stránky s právními informacemi
 * @author Jan Štěch
 */
class LegalController extends Controller
{

    /**
     * Metoda nastavující hlavičku požadavku, titulek stránky a zobrazovaný pohled stránky
     * @see Controller::process()
     */
    function process(array $parameters): void
    {
        $this->pageHeader['title'] = 'Právní informace';
        $this->pageHeader['description'] = 'Zde si můžete přečíst, co od vás za používání naší služby vyžadujeme, jaké jsou naše povinnosti vůči vám, jak nakládáme s vašemi údaji, jaké údaje přesně ukládáme a z jakého důvodu a také jaké soubory cookie ukládáme na vaše zařízení a proč tak činíme.';
        $this->pageHeader['keywords'] = 'podmínky, pravidla, zákon, dokument, právo, práva, povinnosti, soukromí, zásady, údaje, data, cookies, soubor, informace';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js', 'js/legal.js');
        $this->pageHeader['bodyId'] = 'legal';

        $githubFetcher = new GitHubFileFetcher();
        try { $this->data['staticContent'][0] = $githubFetcher->getTermsOfService(); }
        catch (UnexpectedValueException $e) { $this->data['staticContent'][0] = $e->getMessage(); }
        try { $this->data['staticContent'][1] = $githubFetcher->getPrivacyPolicy(); }
        catch (UnexpectedValueException $e) { $this->data['staticContent'][1] = $e->getMessage(); }
        try { $this->data['staticContent'][2] = $githubFetcher->getCookiesInfo(); }
        catch (UnexpectedValueException $e) { $this->data['staticContent'][2] = $e->getMessage(); }

        $this->view = 'legal';
    }
}

