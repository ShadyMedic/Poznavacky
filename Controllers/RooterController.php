<?php
namespace Poznavacky\Controllers;

use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Security\AntiXssSanitizer;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use \BadMethodCallException;
use \ErrorException;

/**
 * Třída směrovače přesměrovávající uživatele z index.php na správný kontroler
 * @author Jan Štěch
 */
class RooterController extends SynchronousController
{
    const CONTROLLER_EXTENSION = 'Controller';
    const CONTROLLER_FOLDER = 'Controllers';
    const DATA_GETTER_EXTENSION = 'DataGetter';
    const DATA_GETTER_FOLDER = 'DataGetters';
    const MODEL_FOLDER = 'Models';
    const VIEW_FOLDER = 'Views';

    private const ROUTES_INI_FILE = 'routes.ini';
    private const INI_ARRAY_SEPARATOR = ',';
    //Význam následujících čtyř konstant je blíže popsán v souboru routes.ini
    private const CLEAR_SELECTION_PREFIX = '-';
    private const IGNORE_SELECTION_VALUE = 'ignore';
    private const NON_SELECTION_VALUE = 'skip';
    private const OR_CHECK_OPERATOR = '?';

    protected ControllerInterface $controllerToCall;

    /**
     * Metoda zpracovávající zadanou URL adresu a přesměrovávající uživatele na zvolený kontroler
     * @param array $parameters Pole parametrů, na indexu 0 musí být nezpracovaná URL adresa
     * @throws AccessDeniedException Při pokusu zkontrolovat nějaký údaj o přihlášeném uživateli, pokud žádný uživatel přihlášen není
     * @throws DatabaseException Pokud se nepodaří nastavit některou ze složek
     */
    public function process(array $parameters): void
    {
        $url = $parameters[0];
        $parsedURL = parse_url($url)['path'];               // Z http(s)://domena.net/abc/def/ghi získá /abc/def/ghi
        $parsedURL = trim($parsedURL, '/');        // Odstranění prvního (a existuje i posledního) lomítka
        $parsedURL = trim($parsedURL);                      // Odstranění mezer na začátku a na konci
        $urlArguments = explode('/', $parsedURL);  // Rozbití řetězce do pole podle lomítek

        if ($urlArguments[0] === '') { $urlArguments = array(); }

        $controllerName = null;

        $iniRoutes = parse_ini_file(self::ROUTES_INI_FILE, true);

        //Rozlišení proměnných a neproměnných URL parametrů
        $controllersUrls = array_keys($iniRoutes['Controllers']);
        $urlVariablesArr = array_diff($urlArguments, $controllersUrls); //Získá pole jednotlivých proměnných URL parametrů
        $urlVariablesPositions = array_keys($urlVariablesArr);
        $urlVariablesValues = array_values($urlVariablesArr);
        for ($i = 0, $j = 0; $i < count($urlArguments); $i++)
        {
            if (in_array($i, $urlVariablesPositions))
            {
                $urlArguments[$i] = '<'.$j.'>';
                $j++; //Číslo proměnné
            }
        }

        //$urlArgumenty nyní obsahuje pole URL parametrů, kde jsou proměnné parametry nahrazeny značkami <x>

        //Nalezení kontroleru k použití
        $path = '/'.implode('/',$urlArguments);
        $controllerName = @$iniRoutes['Routes'][$path];
        if (empty($controllerName))
        {
            //Cesta nenalezena
            $aChecker = new AccessChecker();
            if ($aChecker->checkUser())
            {
                (new Logger(true))->warning('Uživatel s ID {userId} odeslal z IP adresy {ip} požadavek na URL adresu {requestUrl}, avšak daná cesta nebyla v konfiguraci nalezena', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'requestUrl' => $_SERVER['REQUEST_URI']));
            }
            else
            {
                (new Logger(true))->warning('Nepřihlášený uživatel odeslal z IP adresy {ip} požadavek na URL adresu {requestUrl}, avšak daná cesta nebyla v konfiguraci nalezena', array('ip' => $_SERVER['REMOTE_ADDR'], 'requestUrl' => $_SERVER['REQUEST_URI']));
            }
            header('HTTP/1.0 404 Not Found');
            exit();
        }
        $pathToController = $this->classExists($controllerName.self::CONTROLLER_EXTENSION, self::CONTROLLER_FOLDER);
        $this->controllerToCall = new $pathToController;

        //Získání seznamu nastavní složek
        $selections = explode(self::INI_ARRAY_SEPARATOR, @$iniRoutes['Selections'][$path]);
        if (empty($selections[0])) { $selections = array(); }

        //Získání seznamu kontrol, které musí být provedeny
        $shortPath = ''; //Cesta začínající posledním neproměnným kontrolerem
        for ($i = count($urlArguments) - 1; $i >= 0 && !in_array($urlArguments[$i], $controllersUrls); $i--)
        {
            $shortPath = '/'.$urlArguments[$i].$shortPath;
        }
        if ($i >= 0) { $shortPath = '/'.$urlArguments[$i].$shortPath; }
        else { $shortPath = $path; }

        $checks = explode(self::INI_ARRAY_SEPARATOR, @$iniRoutes['Checks'][$shortPath]);
        if (empty($checks[0])) { $checks = array(); }

        //Získání seznamu pohledu pro použití
        self::$views = explode(self::INI_ARRAY_SEPARATOR, @$iniRoutes['Views'][$shortPath]);
        if (empty(self::$views[0])) { self::$views = array(); }

        //Získání seznamu získávačů dat pro pohledy
        $dataGetters = array();
        foreach (self::$views as $view)
        {
            $dataGetterName = @$iniRoutes['DataGetters'][$view];
            if (empty($dataGetterName)) { continue; }
            $pathToDataGetter = $this->classExists($dataGetterName.self::DATA_GETTER_EXTENSION, self::DATA_GETTER_FOLDER);
            $dataGetter = new $pathToDataGetter;
            $dataGetters[] = $dataGetter;
        }

        //Dočasné uložení naparsované URL adresy pro definitivní kontrolery a získávače dat
        $_SESSION['temp']['parsedUrlTemplate'] = $path;
        $_SESSION['temp']['parsedUrl'] = $parsedURL;

        /* Všechny potřebné informace z routes.ini získány */

        //Nastavení složek
        if (!$this->setFolders($selections, $urlVariablesValues)) //Předávání podle reference zajistí, že se spotřebované parametry odeberou
        {
            header('HTTP/1.0 404 Not Found');
            exit();
        }

        //Provedení kontrol
        if (!$this->runChecks($checks))
        {
            header('HTTP/1.0 403 Forbidden');
            exit();
        }

        //Získání dat pro zobrazení v obalových pohledech
        foreach ($dataGetters as $dataGetter)
        {
            self::$data = array_merge(self::$data, $returnedData = $dataGetter->get());
        }

        //Získání dat pro zobrazení v hlavním pohledu
        $this->controllerToCall->process($urlVariablesValues);

        if ($this->controllerToCall instanceof SynchronousController)
        {
            //Synchroní kontroler - nastav hlavičky stránky a základní pohled
            self::$data['title'] = SynchronousController::$pageHeader['title'];
            self::$data['description'] = SynchronousController::$pageHeader['description'];
            self::$data['keywords'] = SynchronousController::$pageHeader['keywords'];
            self::$data['cssFiles'] = SynchronousController::$pageHeader['cssFiles'];
            self::$data['jsFiles'] = SynchronousController::$pageHeader['jsFiles'];
            self::$data['bodyId'] = SynchronousController::$pageHeader['bodyId'];
        }
      # else { /*AJAX kontroler - nezobrazuj žádný pohled*/ }
    }

    /**
     * Metoda pro nastavení objektu třídy, poznávačky nebo části do $_SESSON['selection']
     * Při přenastavení nějaké složky jsou z pole $_SESSON['selection'] vymazány všechny podčásti té stávající
     * @param array $selections Pole řetězců popisující význam a pořadí argumentů ve druhém argumentu, povolené hodnoty řetězců jsou 'class', 'group', 'part' a hodnoty konstant této třídy obsahující v názvu slovo 'SELECTOR'
     * @param array $urlVariablesValues Pole argumentů získaných z URL pro zpracování, pořadí prvků musí odpovídat hodnotám v prvním argumentu
     * @return bool TRUE, pokud se povedlo všechny složky nastavit, FALSE, pokud některá ze složek nebyla podle URL v databázi nalezena
     * @throws DatabaseException Pokud se vyskytne chyba při načítání údajů o složce z databáze
     */
    private function setFolders(array $selections, array &$urlVariablesValues): bool
    {
        $aChecker = new AccessChecker();
        for ($i = 0; $i < count($selections); $i++)
        {
            $selection = $selections[$i];
            if ($selection[0] === self::CLEAR_SELECTION_PREFIX)
            {
                unset($_SESSION['selection'][substr($selection, 1)]);
                continue;
            }
            else if ($selection === self::NON_SELECTION_VALUE) { continue; }
            else if ($selection === self::IGNORE_SELECTION_VALUE)
            {
                array_shift($urlVariablesValues);
                continue;
            }
            $currentUrl = array_shift($urlVariablesValues);

            $folder = null;
            $folderNotFound = false;
            $alreadySet = false;
            switch ($selection)
            {
                case 'class':
                    $parentFolder = null;
                    $alreadySet = ($aChecker->checkClass() && $_SESSION['selection']['class']->getUrl() === $currentUrl);
                    if (!$alreadySet)
                    {
                        $folder = new ClassObject(false, 0);
                        $folder->initialize(null, $currentUrl);
                    }
                    break;
                case 'group':
                    $parentFolder = $_SESSION['selection']['class'];
                    if (!$alreadySet)
                    {
                        $groups = $parentFolder->getGroups();
                        for ($j = 0; $j < count($groups) && $groups[$j]->getUrl() !== $currentUrl; $j++) {}
                        if ($j === count($groups)) { $folderNotFound = true; }
                        else { $folder = $groups[$j]; }
                    }
                    break;
                case 'part':
                    $parentFolder = $_SESSION['selection']['group'];
                    $alreadySet = ($aChecker->checkPart() && $_SESSION['selection']['part']->getUrl() === $currentUrl);
                    if (!$alreadySet)
                    {
                        $parts = $parentFolder->getParts();
                        for ($j = 0; $j < count($parts) && $parts[$j]->getUrl() !== $currentUrl; $j++) {}
                        if ($j === count($parts)) { $folderNotFound = true; }
                        else { $folder = $parts[$j]; }
                    }
                    break;
            }

            //Kontrola, zda není složka se stejným URL již náhodou zvolena
            if ($alreadySet) { continue; }
            try
            {
                if ($folderNotFound) { throw new BadMethodCallException(); }
                $folder->load();
            }
            catch (BadMethodCallException $e)
            {
                //Třída/poznávačka/část splňující daná kritéria neexistuje
                # if ($aChecker->checkUser()) //Uživatel je zde vždy přihlášen - jinak by ho AntiCsrfMiddleware nepustil na adresu vedoucí na menu stránku
                # {
                (new Logger(true))->warning('Uživatel s ID {userId} přistupující do systému z IP adresy {ip} se pokusil vstoupit do třídy, poznávačky, nebo části, která nebyla v databázi nalezena (URL reprezentace {folderUrl}, název úrovně {folderLevel})', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'folderUrl' => $currentUrl, 'folderLevel' => $selection));
                # }
                return false;
            }
            $_SESSION['selection'][$selection] = $folder;

            //Vymaž podsložky přepsané složky
            if ($selection === 'class')
            {
                unset($_SESSION['selection']['group']);
                unset($_SESSION['selection']['part']);
            }
            if ($selection === 'group'){ unset($_SESSION['selection']['part']); }
        }
        return true;
    }

    /**
     * Metoda provádějící všechny bezpečnostní kontroly, například zda je přihlášený uživatel správcem zvolené třídy
     * @param array $checks Pole řetězců popisujících kontroly, které se musejí provést, jejich význam je blíže popsán v souboru routes.ini
     * @return bool TRUE, pokud všechny kontroly proběhnou v pořádku, FALSE, pokud ne
     * @throws AccessDeniedException Pokud je kontrolováno cokoliv o přihlášeném uživateli, pokud žádný uživatel přihlášen není (toto se nemůže stát, pokud jsou kontroly správně seřazey - od nejmírnější po nejpřísnější)
     */
    private function runChecks(array $checks): bool
    {
        $aChecker = new AccessChecker();
        foreach ($checks as $check)
        {
            $subChecks = explode(self::OR_CHECK_OPERATOR, $check);
            $subCheckSuccessfulResults = 0;
            foreach ($subChecks as $subCheck)
            {
                $subCheckResult = true;
                switch ($subCheck)
                {
                    case 'user':
                        # if (!$aChecker->checkUser()) { $subCheckResult = false; }
                        //Tuto kontrolu provádí již AntiCsrfMiddleware, který nepřihlášeného uživatele nepustí na menu stránku
                        break;
                    case 'member':
                        if ($aChecker->checkDemoAccount()) { $subCheckResult = false; }
                        break;
                    case 'systemAdmin':
                        if (!$aChecker->checkSystemAdmin()) { $subCheckResult = false; }
                        break;
                    case 'class':
                        if (!$aChecker->checkClass()) { $subCheckResult = false; }
                        break;
                    case 'classAccess':
                        if (!$_SESSION['selection']['class']->checkAccess(UserManager::getId(), true))
                        {
                            unset($_SESSION['selection']);
                            $subCheckResult = false;
                        }
                        break;
                    case 'classAdmin':
                        if (!($aChecker->checkClass() && $_SESSION['selection']['class']->checkAdmin(UserManager::getId()))) { $subCheckResult = false; }
                        break;
                    case 'group':
                        if (!$aChecker->checkGroup()) { $subCheckResult = false; }
                        break;
                    case 'naturals':
                        if (
                            ($aChecker->checkPart() && $_SESSION['selection']['part']->getNaturalsCount() === 0) ||
                            (!$aChecker->checkPart() && count($_SESSION['selection']['group']->getNaturals()) === 0)
                        ) { $subCheckResult = false; }
                        break;
                }
                if ($subCheckResult === true) { $subCheckSuccessfulResults++; }
            }
            if ($subCheckSuccessfulResults === 0)
            {
                # if ($aChecker->checkUser()) //Uživatel je zde vždy přihlášen - jinak by ho AntiCsrfMiddleware nepustil na adresu vedoucí na menu stránku
                # {
                (new Logger(true))->warning('Uživatel s ID {userId} odeslal z IP adresy {ip} požadavek na adresu {requestUrl}, avšak přístup mu byl odepřen kvůli selhání kontroly typu {check}', array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'requestUrl' => $_SERVER['REQUEST_URI'], 'check' => $check));
                # }
                return false;
            }
        }
        return true;
    }

    /**
     * Metoda navracející cestu k dalšímu (vnitřenějšímu) nepoužitému pohledu
     * Tato metoda je volána z pohledů v místech, kde má být dynamicky vložen vnitřnější pohled
     * @return string Cesta k pohledu, která může být okamžitě použita v příkazu include nebo require
     */
    private function getNextView(): string
    {
        return self::VIEW_FOLDER.'/'.array_shift(self::$views).'.phtml';
    }

    /**
     * Metoda zahrnující pohled a vypysující do něj proměnné z vlastnosti $data
     * Pokud je vlastnost SynchronousController::$view prázdná, není vypsáno nic
     * @throws ErrorException Pokud selže ošetření dat proti XSS útoku
     */
    public function displayView(): void
    {
        if (!empty(self::$views))
        {
            //Vytvoř pole ošetřených hodnot
            $sanitized = $this->sanitizeData(self::$data);

            //Přejmenuj klíče v originálním poli neošetřených hodnot
            foreach (self::$data as $key => $value)
            {
                self::$data['_'.$key.'_'] = $value;
                unset(self::$data[$key]);
            }

            extract(self::$data);
            extract($sanitized);
            require self::VIEW_FOLDER.'/'.array_shift(self::$views).'.phtml';
        }
    }

    /**
     * Metoda ošetřující všechny hodnoty určené k využití pohledem proti XSS útoku
     * @param array $data Pole proměnných k ošetření
     * @return mixed Pole s ošetřenými hodnotami
     * @throws ErrorException Pokud se pokouším ošetřit nepodporovaný datový typ
     */
    private function sanitizeData(array $data): array
    {
        $sanitizer = new AntiXssSanitizer();
        foreach ($data as $key => $value)
        {
            $data[$key] = $sanitizer->sanitize($value);
        }
        return $data;
    }
}

