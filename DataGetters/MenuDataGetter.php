<?php
namespace Poznavacky\DataGetters;

use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Security\AccessChecker;

class MenuDataGetter implements DataGetter
{
    private const NAVIGATION_INI_FILE = 'navigation.ini';
    private const INI_ARRAY_SEPARATOR = ',';
    //Význam následujících dvou konstant je blíže popsán v souboru routes.ini
    private const NO_LINK_NAVIGATION_PREFIX = '#';
    private const SKIP_NAVIGATION_VALUE = 'skip';
    
    /**
     * @inheritDoc
     */
    public function get(): array
    {
        $result = array();
        
        //Načtení zpracované URL adresy z dočasného úložiště
        $parsedUrl = $_SESSION['temp']['parsedUrlTemplate'];
        $iniData = parse_ini_file(self::NAVIGATION_INI_FILE, true);
        
        //Získání seznamu položek pro navigační řádek
        $navigationData = $iniData[$parsedUrl];
        $navigationStrings = explode(self::INI_ARRAY_SEPARATOR, $navigationData);
        $navigationLinks = explode('/', $_SESSION['temp']['parsedUrl']);
        $currentLink = '';
        foreach ($navigationStrings as $navigationItem) {
            $currentLink .= '/'.array_shift($navigationLinks);
            $link = '';
            if ($navigationItem === self::SKIP_NAVIGATION_VALUE) {
                continue;
            }
            if ($navigationItem[0] === self::NO_LINK_NAVIGATION_PREFIX) {
                //Pouze statický text
                $navigationItem = mb_substr($navigationItem, 1);
                $link = '#';
            }
            if (isset($_SESSION['selection'][$navigationItem])) {
                //Název složky jako text
                $text = $_SESSION['selection'][$navigationItem]->getName();
                if ($link !== '#') {
                    $link = $currentLink;
                }
            } else {
                //Jiný text
                $text = $navigationItem;
                if ($link !== '#') {
                    $link = $currentLink;
                }
            }
            
            $result['navigationBar'][] = array('text' => $text, 'link' => $link);
        }
        
        $aChecker = new AccessChecker();
        try {
            $result['adminLogged'] = $aChecker->checkSystemAdmin();
            $result['demoVersion'] = $aChecker->checkDemoAccount();
        } catch (AccessDeniedException $e) {
            //Žádný uživatel není přihlášen (to by se kvůli rootování teoreticky nemohlo stát)
            $result['adminLogged'] = false;
            $result['demoVersion'] = true;
        }
        
        return $result;
    }
}

