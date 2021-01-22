<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\GitHubFileFetcher;
use \UnexpectedValueException;

/**
 * Kontroler stránky se zásadami o ochraně soukromí
 * @author Jan Štěch
 */
class PrivacyPolicyController extends Controller
{

    /**
     * Metoda nastavující hlavičku požadavku, titulek stránky a zobrazovaný pohled stránky
     * @see Controller::process()
     */
    function process(array $parameters): void
    {
        $this->pageHeader['title'] = 'Zásady ochrany soukromí';
        $this->pageHeader['description'] = 'Zde si můžete přečíst, jak nakládáme s vašemi údaji, jaké údaje přesně ukládáme a z jakého důvodu.';
        $this->pageHeader['keywords'] = 'soukromí, zásady, zákon, dokument, právo, údaje, data';
        $this->pageHeader['cssFiles'] = array();
        $this->pageHeader['jsFiles'] = array();
        $this->pageHeader['bodyId'] = 'privacy-policy';

        $githubFetcher = new GitHubFileFetcher();
        $this->data['staticTitle'] = 'Zásady ochrany soukromí';
        try
        {
            $this->data['staticContent'] = $githubFetcher->getPrivacyPolicy();
        }
        catch (UnexpectedValueException $e)
        {
            $this->data['staticContent'] = $e->getMessage();
        }

        $this->view = 'staticArticle';
    }
}