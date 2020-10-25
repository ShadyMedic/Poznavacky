<?php
/**
 * Třída starající se o řešení hlášení z pohledu správce třídy
 * @author Jan Štěch
 */
class ReportResolver
{
    private $group;
    
    /**
     * Konstruktor zajišťující, že instanci této třídy lze vytvořit pouze pokud je přihlášen správce zvolené třídy
     * Také je nastavena poznávačka, ve které je možné vytvořenou instancí řešit hlášení
     * @throws AccessDeniedException V případě, že není vybrána žádná třída nebo skupina nebo pokud přihlášený uživatel není správcem zvolené třídy
     */
    public function __construct()
    {
        if (!isset($_SESSION['selection']['class']))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_CLASS_NOT_CHOSEN);
        }
        $class = $_SESSION['selection']['class'];
        if (!$class->checkAdmin(UserManager::getId()))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }
        
        //Kontrola dat OK
        
        if (!isset($_SESSION['selection']['group']))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_GROUP_NOT_CHOSEN);
        }
        $this->group = $_SESSION['selection']['group'];
    }
    
    /**
     * 
     * Metoda upravující přírodninu a/nebo adresu obrázku uloženého v databázi
     * @param int $pictureId ID obrázku, jehož data chceme změnit
     * @param string $newNaturalName Název nové přírodniny, kterou obrázek zobrazuje
     * @param string $newUrl Nová adresa obrázku
     * @throws AccessDeniedException V případě, že nově zvolená přírodnina nepatří do té samé poznávačky, jako ta stávající
     */
    public function editPicture(int $pictureId, string $newNaturalName, string $newUrl)
    {
        $picture = new Picture(false, $pictureId);
        $natural = new Natural(false, 0);
        $natural->initialize($newNaturalName, null, null, null, $this->group, null);
        
        try
        {
            $natural->load();   //Pokud není v té samé poznávačce nalezena přírodnina se stejným jménem, je vyhozena výjimka
        }
        catch (NoDataException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_REPORTS_EDIT_PICTURE_ANOTHER_GROUP);
        }
        
        $picture->updatePicture($natural, $newUrl);
        $picture->save();
    }
    
    /**
     * Metoda skrývající obrázek s daným ID z databáze i se všemi jeho hlášeními
     * @param int $pictureId ID obrázku k odstranění
     */
    public function disablePicture(int $pictureId)
    {
        $picture = new Picture(false, $pictureId);
        $picture->disable();
        $picture->deleteReports();
    }
    
    /**
     * Metoda odstraňující obrázek s daným ID z databáze i se všemi jeho hlášeními
     * @param int $pictureId ID obrázku k odstranění
     */
    public function deletePicture(int $pictureId)
    {
        $picture = new Picture(false, $pictureId);
        $picture->delete();
    }
    
    /**
     * Metoda odstraňující hlášení s daným ID z databáze
     * @param int $reportId ID hlášení k odstranění
     */
    public function deleteReport(int $reportId)
    {
        $report = new Report(false, $reportId);
        $report->delete();
    }
}