<?php
/** 
 * Třída starající se o zpracování dat odeslaných z formuláře pro přidání obrázku a případné uložení obrázku do databáze
 * @author Jan Štěch
 */
class ReportAdder
{
    private $group;
    
    /**
     * Konstruktor třídy nastavující poznávačku, z níž pochází nahlašovaný obrázek
     * Všechny ostatní údaje se předávají metodě proccessFormData - všechna taková data pocházejí z $_POST
     * @param Group $group Objekt poznávačky, z níž uživatel nahlašuje obrázek
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }
    
    /**
     * Metoda zpracovávající data odeslaná z formuláře pro nahlášení obrázku
     * Data jsou ověřena a posléze i uložena do databáze, nebo je vyvolána výjimka s chybovou hláškou
     * @param array $POSTdata Pole dat odeslaných z formuláře
     * @throws AccessDeniedException V případě že data nesplňují podmínky
     * @return boolean TRUE, pokud je úspěšně uloženo nové hlášení
     */
    public function processFormData(array $POSTdata)
    {
        $url = $_POST['picUrl'];
        $reason = $_POST['reason'];
        $additionalInformation = $_POST['info'];
        
        //Kontrola, zda je zadaný důvod platný
        $availableReasons = REPORT::ALL_REASONS;
        
        if (!in_array($reason, $availableReasons, true))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_REPORT_INVALID_REASON, null, null);
        }
        
        //Kontrola vyplnění dodatečných informací (jsou-li potřeba)
        if ($reason === Report::REASON_LONG_LOADING)
        {
            //Kontrola, zda je specifikován jeden z časových intervalů
            if (!in_array($additionalInformation, Report::LONG_LOADING_AVAILABLE_DELAYS))
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_REPORT_INVALID_ADDITIONAL_INFORMATION, null, null);
            }
        }
        if ($reason === Report::REASON_OTHER || $reason === Report::REASON_OTHER_ADMIN)
        {
            if (!mb_strlen($additionalInformation) > 0)
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_REPORT_INVALID_ADDITIONAL_INFORMATION, null, null);
            }
        }
        
        //Nastavit správnou přírodninu na "Nezadáno" v případě, že důvod nahlášení je nesprávná přírodnina, ale nebylo zadáno, jaká je správná
        if ($reason === Report::REASON_INCORRECT_NATURAL && mb_strlen($additionalInformation) === 0)
        {
            $additionalInformation = Report::INCORRECT_NATURAL_DEFAULT_INFO;
        }
        
        //Získání objektu přírodniny
        Db::connect();
        $dbResult = Db::fetchQuery('
        SELECT
        '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['id'].', '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['name'].', '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['picturesCount'].', 
        '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['id'].', '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['natural'].', '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['src'].', '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['enabled'].'
        FROM '.Picture::TABLE_NAME.'
        JOIN '.Natural::TABLE_NAME.' ON '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['natural'].' = '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['id'].'
        WHERE '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['src'].' = ? AND '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['id'].' IN (
            SELECT prirodniny_id FROM prirodniny_casti WHERE casti_id IN (
                SELECT '.Part::COLUMN_DICTIONARY['id'].' FROM '.Part::TABLE_NAME.' WHERE '.Part::COLUMN_DICTIONARY['group'].' = ?
            )
        );
        ', array($url, $this->group->getId()), false);
        
        //Obrázek nebyl v databázi podle zdroje nalezen
        if ($dbResult === false)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_REPORT_UNKNOWN_PICTURE, null, null);
        }
        
        $natural = new Natural(false, $dbResult[Natural::COLUMN_DICTIONARY['id']]);
        $natural->initialize($dbResult[Natural::COLUMN_DICTIONARY['name']], null, $dbResult[Natural::COLUMN_DICTIONARY['picturesCount']], null);
        $picture = new Picture(false, $dbResult[Picture::COLUMN_DICTIONARY['id']]);
        $picture->initialize($url, $natural, $dbResult[Picture::COLUMN_DICTIONARY['enabled']], null);
        
        $report = new Report(false, 0);    //Pokus s hlášením, které již v datbázi existuje, ale u kterého neznáme ID
        $report->initialize($picture, $reason, $additionalInformation, null);
        try
        {
            $report->load();    //Pokud hlášení zatím v databázi neexistuje, je vyvolána výjimka typu NoDataException
            $report->increaseReportersCount();  //Zvýšení počtu hlášení tohoto typu o 1
        }
        catch (NoDataException $e)
        {
            $report = new Report(true); //Tvorba nového hlášení
            $report->initialize($picture, $reason, $additionalInformation, 1);
        }
        $report->save();    //Uložení hlášení do databáze
    }
}