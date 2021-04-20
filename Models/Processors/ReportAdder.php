<?php
namespace Poznavacky\Models\Processors;

use Poznavacky\Models\DatabaseItems\Group;
use Poznavacky\Models\DatabaseItems\Natural;
use Poznavacky\Models\DatabaseItems\Part;
use Poznavacky\Models\DatabaseItems\Picture;
use Poznavacky\Models\DatabaseItems\Report;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use \BadMethodCallException;

/**
 * Třída starající se o zpracování dat odeslaných z formuláře pro přidání obrázku a případné uložení obrázku do databáze
 * @author Jan Štěch
 */
class ReportAdder
{
    private Group $group;
    
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
     * @return boolean TRUE, pokud je úspěšně uloženo nové hlášení
     * @throws DatabaseException Pokud se vyskytne chyba při práci s databází
     * @throws AccessDeniedException V případě že data nesplňují podmínky
     */
    public function processFormData(array $POSTdata): bool
    {
        $url = $POSTdata['picUrl'];
        $reason = $POSTdata['reason'];
        $additionalInformation = @$POSTdata['info'];
        
        //Kontrola, zda je zadaný důvod platný
        $availableReasons = Report::ALL_REASONS;
        
        if (!in_array($reason, $availableReasons, true)) {
            (new Logger(true))->warning('Uživatel s ID {userId} se pokusil odeslat hlášení obrázku s URL {picUrl} v poznávačce s ID {groupId}, avšak nespecifikoval platný důvod',
                array('userId' => UserManager::getId(), 'picUrl' => $url, 'groupId' => $this->group->getId()));
            throw new AccessDeniedException(AccessDeniedException::REASON_REPORT_INVALID_REASON, null, null);
        }
        
        //Kontrola vyplnění dodatečných informací (jsou-li potřeba)
        $insufficientAdditionalInformation = false;
        if ($reason === Report::REASON_LONG_LOADING) {
            //Kontrola, zda je specifikován jeden z časových intervalů
            if (!in_array($additionalInformation, Report::LONG_LOADING_AVAILABLE_DELAYS)) {
                $insufficientAdditionalInformation = true;
            }
        }
        if ($reason === Report::REASON_OTHER || $reason === Report::REASON_OTHER_ADMIN) {
            if (!mb_strlen($additionalInformation) > 0) {
                $insufficientAdditionalInformation = true;
            }
        }
        
        if ($insufficientAdditionalInformation) {
            (new Logger(true))->warning('Uživatel s ID {userId} se pokusil odeslat hlášení obrázku s URL {picUrl} v poznávačce s ID {groupId} z důvodu {reason}, avšak nevyplnil správně dodatečné informace',
                array(
                    'userId' => UserManager::getId(),
                    'picUrl' => $url,
                    'groupId' => $this->group->getId(),
                    'reason' => $reason
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_REPORT_INVALID_ADDITIONAL_INFORMATION, null,
                null);
        }
        
        //Nastavit správnou přírodninu na "Nezadáno" v případě, že důvod nahlášení je nesprávná přírodnina, ale nebylo zadáno, jaká je správná
        if ($reason === Report::REASON_INCORRECT_NATURAL && mb_strlen($additionalInformation) === 0) {
            $additionalInformation = Report::INCORRECT_NATURAL_DEFAULT_INFO;
        }
        
        //Získání objektu přírodniny
        $dbResult = Db::fetchQuery('
        SELECT
        '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['id'].', '.Natural::TABLE_NAME.'.'.
                                   Natural::COLUMN_DICTIONARY['name'].', '.Natural::TABLE_NAME.'.'.
                                   Natural::COLUMN_DICTIONARY['picturesCount'].',
        '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['id'].', '.Picture::TABLE_NAME.'.'.
                                   Picture::COLUMN_DICTIONARY['natural'].', '.Picture::TABLE_NAME.'.'.
                                   Picture::COLUMN_DICTIONARY['src'].', '.Picture::TABLE_NAME.'.'.
                                   Picture::COLUMN_DICTIONARY['enabled'].'
        FROM '.Picture::TABLE_NAME.'
        JOIN '.Natural::TABLE_NAME.' ON '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['natural'].' = '.
                                   Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['id'].'
        WHERE '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['src'].' = ? AND '.Natural::TABLE_NAME.'.'.
                                   Natural::COLUMN_DICTIONARY['id'].' IN (
            SELECT prirodniny_id FROM prirodniny_casti WHERE casti_id IN (
                SELECT '.Part::COLUMN_DICTIONARY['id'].' FROM '.Part::TABLE_NAME.' WHERE '.
                                   Part::COLUMN_DICTIONARY['group'].' = ?
            )
        );
        ', array($url, $this->group->getId()), false);
        
        //Obrázek nebyl v databázi podle zdroje nalezen
        if ($dbResult === false) {
            (new Logger(true))->warning('Uživatel s ID {userId} se pokusil odeslat hlášení obrázku s URL {picUrl} v poznávačce s ID {groupId} z důvodu {reason}, avšak obrázek nemohl být v databázi nalezen',
                array(
                    'userId' => UserManager::getId(),
                    'picUrl' => $url,
                    'groupId' => $this->group->getId(),
                    'reason' => $reason
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_REPORT_UNKNOWN_PICTURE, null, null);
        }
        
        $natural = new Natural(false, $dbResult[Natural::COLUMN_DICTIONARY['id']]);
        $natural->initialize($dbResult[Natural::COLUMN_DICTIONARY['name']], null,
            $dbResult[Natural::COLUMN_DICTIONARY['picturesCount']]);
        $picture = new Picture(false, $dbResult[Picture::COLUMN_DICTIONARY['id']]);
        $picture->initialize($url, $natural, $dbResult[Picture::COLUMN_DICTIONARY['enabled']]);
        
        $report = new Report(false, 0);    //Pokus s hlášením, které již v datbázi existuje, ale u kterého neznáme ID
        $report->initialize($picture, $reason, $additionalInformation);
        try {
            $report->load();    //Pokud hlášení zatím v databázi neexistuje, je vyvolána výjimka typu BadMethodCallException
            $report->increaseReportersCount();  //Zvýšení počtu hlášení tohoto typu o 1
            $result = $report->save();    //Uložení hlášení do databáze
            (new Logger(true))->info('Uživatel s ID {userId} odeslal hlásení obrázku s URL {picUrl} v poznávačce s ID {groupId} z důvodu {reason} (hlášení tohoto typu již existuje a tak byl pouze zvýšen počet nahlašovatelů)',
                array(
                    'userId' => UserManager::getId(),
                    'picUrl' => $url,
                    'groupId' => $this->group->getId(),
                    'reason' => $reason
                ));
        } catch (BadMethodCallException $e) {
            $report = new Report(true); //Tvorba nového hlášení
            $report->initialize($picture, $reason, $additionalInformation, 1);
            $result = $report->save();    //Uložení hlášení do databáze
            (new Logger(true))->info('Uživatel s ID {userId} odeslal hlásení obrázku s URL {picUrl} v poznávačce s ID {groupId} z důvodu {reason}',
                array(
                    'userId' => UserManager::getId(),
                    'picUrl' => $url,
                    'groupId' => $this->group->getId(),
                    'reason' => $reason
                ));
        }
        return $result;
    }
}

