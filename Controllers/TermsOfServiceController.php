<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\GitHubFileFetcher;
use \UnexpectedValueException;

/**
 * Kontroler stránky s podmínkami služby
 * @author Jan Štěch
 */
class TermsOfServiceController extends Controller
{

    /**
     * Metoda nastavující hlavičku požadavku, titulek stránky a zobrazovaný pohled stránky
     * @see Controller::process()
     */
    function process(array $parameters): void
    {
        $this->pageHeader['title'] = 'Podmínky služby';
        $this->pageHeader['description'] = 'Zde si můžete přečíst, co od vás za používání naší služby vyžadujeme a jaké jsou naše povinnosti vůči vám.';
        $this->pageHeader['keywords'] = 'podmínky, pravidla, zákon, dokument, právo, práva, povinnosti';
        $this->pageHeader['cssFiles'] = array();
        $this->pageHeader['jsFiles'] = array();
        $this->pageHeader['bodyId'] = 'terms-of-service';

        $githubFetcher = new GitHubFileFetcher();
        $this->data['staticTitle'] = 'Podmínky služby';
        try
        {
            $this->data['staticContent'] = $githubFetcher->getTermsOfService();
        }
        catch (UnexpectedValueException $e)
        {
            $this->data['staticContent'] = $e->getMessage();
        }

        $this->view = 'staticArticle';
    }
}