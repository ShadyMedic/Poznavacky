<?php
namespace Poznavacky\Controllers;

/**
 * Kontroler stránky s informacemi o souborech cookie
 * @author Jan Štěch
 */
class CookiesController extends Controller
{

    /**
     * Metoda nastavující hlavičku požadavku, titulek stránky a zobrazovaný pohled stránky
     * @see Controller::process()
     */
    function process(array $parameters): void
    {
        $this->pageHeader['title'] = 'Soubory cookie';
        $this->pageHeader['description'] = 'Zajímá vás, jaké soubory cookie ukládáme na vaše zařízení, proč tak činíme a jak vás to ovlivňuje? Poté jste na správné adrese.';
        $this->pageHeader['keywords'] = 'cookies, soukromí, soubor, dokument, informace';
        $this->pageHeader['cssFiles'] = array();
        $this->pageHeader['jsFiles'] = array();
        $this->pageHeader['bodyId'] = 'cookies';

        $this->view = 'cookies';
    }
}

