<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\MessageBox;

/**
 * Obecný kontroler pro nastavování hlaviček zobrazitelných stránek a zpracování informací na ně odeslaných
 * Mateřská třída všech kontrolerů nastavujících pohled
 */
abstract class SynchronousController implements ControllerInterface
{
    //Informace pro zobrazení v pohledech - statické, protože každý požadavek vede k zobrazení maximálně jedné stránky
    protected static array $data = array();
    protected static array $pageHeader = array('title' => 'Poznávačky', 'keywords' => '', 'description' => '', 'cssFile' => array(), 'jsFile' => array());
    protected static array $views = array();

    /**
     * Metoda přesměrovávající uživatele na jinou adresu a ukončující běh skriptu
     * @param string $url
     */
    public function redirect(string $url): void
    {
        header('Location: /'.$url);
        header('Connection: close');
        exit();
    }

    /**
     * Metoda přidávající do sezení nový objekt s hláškou pro uživatele pro zobrazení na příští načtené stránce
     * @param int $type
     * @param string $msg
     */
    protected function addMessage(int $type, string $msg): void
    {
        $messageBox = new MessageBox($type, $msg);
        if (isset($_SESSION['messages']))
        {
            $_SESSION['messages'][] = $messageBox;
        }
        else
        {
            $_SESSION['messages'] = array($messageBox);
        }
    }

    /**
     * Metoda kontrolující, zda ve složce ukládající kontrolery nebo získávače dat existuje daný soubor a v případě že ano, vrací jeho celé jméno (obsahující jeho jmenný prostor)
     * Kód této metody byl z části inspirován touto odpovědi na StackOverflow https://stackoverflow.com/a/44315881/14011077
     * @param string $className Jméno kontroleru nebo získávače dat, který hledáme
     * @param string $directory Složka, ve které má probíhat hledání
     * @return string Plné jméno třídy kontroleru
     */
    protected function classExists(string $className, string $directory): string
    {
        $fileName = $className.'.php';
        $files = scandir($directory, SCANDIR_SORT_NONE);
        foreach($files as $value)
        {
            if ($value === '.' || $value === '..') { continue; }
            $path = realpath($directory.DIRECTORY_SEPARATOR.$value);

            if (!is_dir($path) && $fileName == $value) //Soubor
            {
                //Ořízni cestu vedoucí ke složce obsahující kontrolery
                $rootNamespace = explode('\\', __NAMESPACE__)[0];
                $path = mb_substr($path, mb_strpos($path, $rootNamespace));
                //Nahraď běžná lomítka (v adresářové struktuře) zpětnými lomítky (navigace jmenými prostory)
                $path = str_replace('/', '\\', $path);
                //Ořízni příponu zdrojového souboru
                $path = mb_substr($path, 0, mb_strpos($path, '.')); //Odstřihnutí přípony
                return $path;
            }
            else if (is_dir($path)) //Složka
            {
                //Nalezena složka --> proveď na ní rekurzivní vyhledávání stejnou metodou
                $resultFromSubdirectory = $this->classExists($className, $path);
                //Pokud byl soubor nalezen, navrať cestu k němu, jinak pokračuj v prohledávání souborů v současném adresáři
                if ($resultFromSubdirectory) { return $resultFromSubdirectory; }
            }
        }
        return false;
    }
}

