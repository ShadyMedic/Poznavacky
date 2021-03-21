<?php
namespace Poznavacky\Models;

use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\DatabaseItems\Group;
use Poznavacky\Models\DatabaseItems\Natural;
use Poznavacky\Models\DatabaseItems\Picture;
use Poznavacky\Models\DatabaseItems\Report;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\UserManager;

/**
 * Třída starající se o řešení hlášení z pohledu správce třídy
 * @author Jan Štěch
 */
class ReportResolver
{
    private Classobject $class;
    private Group $group;
    private bool $adminIsLogged = false;

    /**
     * Konstruktor zajišťující, že instanci této třídy lze vytvořit pouze pokud je přihlášen správce zvolené třídy
     * Také je nastavena poznávačka, ve které je možné vytvořenou instancí řešit hlášení a třída, do které musí spadat přírodniny, k jejímž obrázkům se všechna hlášení vztahují
     * @throws AccessDeniedException V případě, že není vybrána žádná třída nebo skupina nebo pokud přihlášený uživatel není správcem zvolené třídy
     * @throws DatabaseException
     */
    public function __construct()
    {
        $checker = new AccessChecker();
        if ($checker->checkSystemAdmin()) { $this->adminIsLogged = true; } //Kontroler je zavolán z administrate stránky

        if (!($this->adminIsLogged || isset($_SESSION['selection']['class'])))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_CLASS_NOT_CHOSEN);
        }
        $class = @$_SESSION['selection']['class'];
        if (!($this->adminIsLogged || $class->checkAdmin(UserManager::getId())))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }

        //Kontrola dat OK
        if (!isset($_SESSION['selection']['group']))
        {
            if (!$this->adminIsLogged)
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_GROUP_NOT_CHOSEN);
            }
        }
        else
        {
            //Tyto dvě vlastnosti jsou potřeba při správě hlášení na stránce reports, ne na administrate
            //Vlastnosti jsou potřeba při úpravách dat obrázku (což není na administrate stránce možné)
            //a dále při kontrole, zda obrázek patří do spravované třídy (což u systémových administrátorů není třeba řešit)
            $this->class = $class;
            $this->group = $_SESSION['selection']['group'];
        }
    }

    /**
     *
     * Metoda upravující přírodninu a/nebo adresu obrázku uloženého v databázi
     * @param int $pictureId ID obrázku, jehož data chceme změnit
     * @param string $newNaturalName Název nové přírodniny, kterou obrázek zobrazuje
     * @param string $newUrl Nová adresa obrázku
     * @throws AccessDeniedException V případě, že nově zvolená přírodnina nepatří do té samé poznávačky, jako ta stávající
     * @throws DatabaseException
     */
    public function editPicture(int $pictureId, string $newNaturalName, string $newUrl): void
    {
        $picture = new Picture(false, $pictureId);

        //Kontrola, zda je vypínaný obrázek součástí nějaké přírodniny patřící do spravované třídy, nebo zda je přihlíšen systémový administrátor
        if (!($this->adminIsLogged || $this->checkPictureBelongsToClass($picture)))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_REPORTS_RESOLVE_PICTURE_FOREIGN_NATURAL);
        }
        
        $natural = new Natural(false, 0);
        $natural->initialize($newNaturalName, null, null, $this->class);
        
        try
        {
            $picture->updatePicture($natural, $newUrl, $this->group);
        }
        catch (AccessDeniedException $e)
        {
            if ($e->getMessage() === AccessDeniedException::REASON_ADD_PICTURE_UNKNOWN_NATURAL)
            {
                //Nahraď hlášku
                throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_REPORTS_EDIT_PICTURE_ANOTHER_GROUP);
            }
            else
            {
                //Nech hlášku tak, jak je
                throw $e;
            }
        }
        
        $picture->save();
    }

    /**
     * Metoda odstraňující obrázek s daným ID z databáze i se všemi jeho hlášeními
     * @param int $pictureId ID obrázku k odstranění
     * @throws AccessDeniedException Pokud obrázek nepatří k přírodnině, která je součástí zvolené třídy
     * @throws DatabaseException
     */
    public function deletePicture(int $pictureId): void
    {
        $picture = new Picture(false, $pictureId);
        //Kontrola, zda je odstraňovaný obrázek součástí nějaké přírodniny patřící do spravované třídy, nebo zda je přihlíšen systémový administrátor
        if (!($this->adminIsLogged || $this->checkPictureBelongsToClass($picture)))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_REPORTS_RESOLVE_PICTURE_FOREIGN_NATURAL);
        }
        $picture->delete();
    }

    /**
     * Metoda odstraňující hlášení s daným ID z databáze
     * @param int $reportId ID hlášení k odstranění
     * @throws AccessDeniedException Pokud hlášení nepatří k obrázku, který nepatří k přírodnině, která je součástí zvolené třídy
     * @throws DatabaseException
     */
    public function deleteReport(int $reportId): void
    {
        $report = new Report(false, $reportId);
        //Kontrola, zda se odstraňované hlášení vztahuje k obrázku, který je součástí nějaké přírodniny patřící do spravované třídy, nebo zda je přihlíšen systémový administrátor
        if (!($this->adminIsLogged || $this->checkPictureBelongsToClass($report->getPicture())))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_REPORTS_RESOLVE_PICTURE_FOREIGN_NATURAL);
        }
        $report->delete();
    }

    /**
     * Metoda kontrolující, zda je daný obrázek přiřazen k přírodnině, která je součástí nějaké poznávačky spravované třídy
     * @param Picture $picture Obrázek pro kontrolu
     * @return bool TRUE, pokud je obrázek součástí nějaké poznávačky patřící do spravované třídy, FALSE, pokud ne
     * @throws DatabaseException
     */
    private function checkPictureBelongsToClass(Picture $picture): bool
    {
        return ($picture->getNatural()->getClass()->getId() === $this->class->getId());
    }
}

