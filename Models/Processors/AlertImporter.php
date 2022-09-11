<?php

namespace Poznavacky\Models\Processors;

use DateTime;
use Poznavacky\Models\DatabaseItems\Alert;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Logger;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\Statics\UserManager;
use RuntimeException;

/**
 * Třída starající se o import chybových hlášení z logovacího souboru
 * @author Jan Štěch
 */
class AlertImporter
{

    /**
     * Logovací soubory, z nichž se mají importovat chybová hlášení
     */
    const SOURCE_LOG_FILES = array(
        Logger::EMERGENCY_LOG_FILE,
        Logger::ALERT_LOG_FILE,
        Logger::CRITICAL_LOG_FILE,
        Logger::ERROR_LOG_FILE
    );

    /**
     * Metoda importující nová chybová hlášení z logovacího souboru do databáze
     * @return int|null Počet naimportovaných chybových hlášení, nebo NULL v případě neúspěchu
     * @throws AccessDeniedException
     * @throws DatabaseException
     */
    public function importAlerts(): ?int
    {
        $logFiles = array_unique(self::SOURCE_LOG_FILES);
        $parsedAlerts = array();
        $query = '
            INSERT INTO '.Alert::TABLE_NAME.'('.
            Alert::COLUMN_DICTIONARY['time'].','.
            Alert::COLUMN_DICTIONARY['level'].','.
            Alert::COLUMN_DICTIONARY['content'].'
            )
        VALUES ';

        foreach ($logFiles as $logFile) {
            //Zkopíruj soubor, aby nebyl blokován zápis nových logovacích záznamů
            if (!copy($logFile, $logFile.'.tmp')) {
                (new Logger())->error('Správce systému s ID {id} se pokusil naimportovat nová chybová hlášení z logovacích souborů do databáze z IP adresy {ip}, avšak došlo k chybě při kopírování logovacího souboru {file}',
                    array(
                        'id' => UserManager::getId(),
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'file' => $logFile
                    )
                );
                throw new RuntimeException('Soubor '.$logFile.' nemohl být zkopírován.');
            }
            $logFile = $logFile.'.tmp';

            //Otevři kopii logu
            $file = fopen($logFile, 'r');
            if ($file === false) {
                (new Logger())->error('Správce systému s ID {id} se pokusil naimportovat nová chybová hlášení z logovacích souborů do databáze z IP adresy {ip}, avšak došlo k chybě při otevírání kopie logovacího souboru {file}',
                    array(
                        'id' => UserManager::getId(),
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'file' => $logFile
                    )
                );
                throw new RuntimeException('Nepodařilo se otevřít soubor '.$logFile);
            }

            //Získej čas nejnovějšího importovaného hlášení
            $result = Db::fetchQuery(
                'SELECT '.Alert::COLUMN_DICTIONARY['time'].
                ' FROM '.Alert::TABLE_NAME.
                ' ORDER BY '.Alert::COLUMN_DICTIONARY['time']. ' DESC'.
                ' LIMIT 1', array());
            if ($result === false) {
                //Zatím žádné záznamy
                $latestImported = new DateTime('0000-01-01 00:00:00');
            } else {
                $latestImported = new DateTime($result[Alert::COLUMN_DICTIONARY['time']]);
            }

            //Iteruj souborem, dokud nenalezneš hlášení vytvořené po datu posledního importovaného hlášení
            $foundBorder = false;
            while (($alert = fgets($file)) !== false) {
                if (empty($alert)) {
                    //Ošetření pro případ prázdného logovacího souboru obsahující pouze jednu prázdnou řádku
                    continue;
                }

                # Formát řetězce v $alert: "[<TIME>] <LEVEL> <ALERT CONTENT>" (without the < and > signs)

                //Extrahuj čas
                $timestampOpeningCharacterPosition = mb_strpos($alert, '['); // [ před časem
                $timestampClosingCharacterPosition = mb_strpos($alert, ']'); // ] za časem
                $time = mb_substr(
                    $alert,
                    $timestampOpeningCharacterPosition + 1,
                    $timestampClosingCharacterPosition - $timestampOpeningCharacterPosition - 1
                );

                if (!$foundBorder) {
                    if (new DateTime($time) > $latestImported) {
                        //Nalezli jsme nejstarší nenaimportované hlášení
                        $foundBorder = true;
                    } else {
                        //Toto hlášení je již naimportováno
                        continue;
                    }
                }

                //Extrahuj úroveň hlášení
                $levelOpeningCharacterPosition = mb_strpos($alert, ' ', $timestampClosingCharacterPosition); //Mezera před úrovní
                $levelClosingCharacterPosition = mb_strpos($alert, ' ', $timestampClosingCharacterPosition + 2); //Mezera za úrovní
                $level = mb_substr(
                    $alert,
                    $levelOpeningCharacterPosition + 1,
                    $levelClosingCharacterPosition - $levelOpeningCharacterPosition
                );

                //Extrahuj obsah
                $content = mb_substr($alert, $levelClosingCharacterPosition);

                //Připrav proměnné pro úpravu databáze
                $parsedAlerts[] = trim($time);
                $parsedAlerts[] = trim($level);
                $parsedAlerts[] = trim($content);
                $query .= '(?,?,?),';
            }

            fclose($file);

            //Odstraň kopii logu, se kterou jsme pracovali
            if (unlink($logFile) === false) {
                (new Logger())->error('Správce systému s ID {id} se pokusil naimportovat nová chybová hlášení z logovacích souborů do databáze z IP adresy {ip}, avšak došlo k chybě při odstraňování dočasné kopie logovacího souboru {file}',
                    array(
                        'id' => UserManager::getId(),
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'file' => $logFile
                    )
                );
                throw new RuntimeException('Nepodařilo se odstranit dočasný soubor '.$logFile);
            }
        }

        if (count($parsedAlerts) > 0) {
            $query = substr($query, 0, strlen($query) - 1).';'; //Nahraď poslední čárku středníkem
            Db::executeQuery($query, $parsedAlerts);

            (new Logger())->info('Správce systému s ID {id} naimportoval {count} nových chybových hlášení z logovacích souborů do databáze z IP adresy {ip}',
                array(
                    'id' => UserManager::getId(),
                    'count' => count($parsedAlerts) / 3,
                    'ip' => $_SERVER['REMOTE_ADDR']
                )
            );
        } else {
            (new Logger())->notice('Správce systému s ID {id} spustil import nových chybových hlášení do databáze z IP adresy {ip}, avšak žádné hlášení k importu nebylo nalezeno',
                array(
                    'id' => UserManager::getId(),
                    'ip' => $_SERVER['REMOTE_ADDR']
                )
            );
        }

        return count($parsedAlerts) / 3;
    }
}