<?php
namespace Poznavacky\Controllers;

use Poznavacky\Controllers\ControllerInterface;
use Poznavacky\Models\MessageBox;
use Poznavacky\Models\Security\AntiXssSanitizer;

/**
 * Obecný kontroler pro nastavování hlaviček zobrazitelných stránek a zpracování informací na ně odeslaných
 * Mateřská třída všech kontrolerů nastavujících pohled
 */
abstract class SynchronousController implements ControllerInterface
{
    const CONTROLLER_EXTENSION = 'Controller';
    const CONTROLLER_FOLDER = 'Controllers';
    const MODEL_FOLDER = 'Models';
    const VIEW_FOLDER = 'Views';

    protected ControllerInterface $controllerToCall;
    //Informace pro zobrazení v pohledech - statické, protože každý požadavek vede k zobrazení maximálně jedné stránky
    protected static array $data = array();
    protected static array $pageHeader = array('title' => 'Poznávačky', 'keywords' => '', 'description' => '', 'cssFile' => array(), 'jsFile' => array());

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
     * Metoda konvertující řetězec v kebab-case do CamelCase
     * @param string $str Řetězec ke konverzi
     * @param bool $capitalizeFirst Má být první písmeno velké (default TRUE)
     * @return string Řetězec konvertovaný do CamelCase
     */
    protected function kebabToCamelCase(string $str, bool $capitalizeFirst = true): string
    {
        $camel = str_replace('-', ' ', $str);
        $camel = ucwords($camel);
        $camel = str_replace(' ', '', $camel);
        if (!$capitalizeFirst){ $camel = lcfirst($camel); }
        return $camel;
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
     * Metoda kontrolující, zda ve složce ukládající kontrolery existuje daný soubor a v případě že ano, vrací jeho celé jméno (obsahující jeho jmenný prostor)
     * Kód této metody byl z části inspirován touto odpovědi na StackOverflow https://stackoverflow.com/a/44315881/14011077
     * @param string $controllerName Jméno kontroleru, který hledáme
     * @param string $directory Složka, ve které má probíhat hledání, základně kořenová složka kontrolerů
     * @return string Plné jméno třídy kontroleru
     */
    protected function controllerExists(string $controllerName, string $directory = self::CONTROLLER_FOLDER): string
    {
        $fileName = $controllerName.'.php';
        $files = scandir($directory, SCANDIR_SORT_NONE);
        foreach($files as $value)
        {
            $path = realpath($directory.DIRECTORY_SEPARATOR.$value);
            $pathWithoutExtensionIfThereIsAny = mb_substr($path, 0, ((mb_strpos($path, '.') === false) ? mb_strlen($path) : mb_strpos($path, '.'))); //Předem se omlouvám

            if (!is_dir($path) && $fileName == $value) //Soubor
            {
                //Ořízni cestu vedoucí ke složce obsahující kontrolery
                $path = mb_substr($path, mb_strpos($path, __NAMESPACE__));
                //Nahraď běžná lomítka (v adresářové struktuře) zpětnými lomítky (navigace jmenými prostor)
                $path = str_replace('/', '\\', $path);
                //Ořízni příponu zdrojového souboru
                $path = mb_substr($path, 0, mb_strpos($path, '.')); //Odstřihnutí přípony
                return $path;
            }
            else if (is_dir($path) && !($value === '.' || $value === '..' || empty($pathWithoutExtensionIfThereIsAny))) //Složka
            {
                //Nalezena složka --> proveď na ní rekurzivní vyhledávání stejnou metodou
                $resultFromSubdirectory = $this->controllerExists($controllerName, $pathWithoutExtensionIfThereIsAny);
                //Pokud byl soubor nalezen, navrať cestu k němu, jinak pokračuj v prohledávání souborů v současném adresáři
                if ($resultFromSubdirectory) { return $resultFromSubdirectory; }
            }
        }
        return false;
    }
}

