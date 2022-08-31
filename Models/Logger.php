<?php

namespace Poznavacky\Models;

use Psr\Log\InvalidArgumentException as LoggerInvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use DateTime;
use Exception;

/**
 * Třída sloužící k zaznamenání všech příchozích akcí při přijetí HTTP požadavku
 * @author Jan Štěch
 */
class Logger implements LoggerInterface
{
    private const EMERGENCY_LOG_FILE = 'log/errors.log';
    private const ALERT_LOG_FILE = 'log/errors.log';
    private const CRITICAL_LOG_FILE = 'log/errors.log';
    private const ERROR_LOG_FILE = 'log/errors.log';
    private const WARNING_LOG_FILE = 'log/warnings.log';
    private const NOTICE_LOG_FILE = 'log/info.log';
    private const INFO_LOG_FILE = 'log/info.log';
    private const DEBUG_LOG_FILE = 'log/debug.log';
    private const TIME_FORMAT = 'Y-m-d H:i:s';
    
    /**
     * Metoda pro zapsání manuálně nastavené zprávy do logovacího souboru
     * Před zprávu je jako u automatického logování vložen datum a čas zápisu a na konec je vložen konec řádku
     * @param string $level Typ zprávy, musí být jedna z konstant třídy Psr\Log\LogLevel
     * @param string $message Zpráva pro zapsání do logovacího souboru
     * @param array $context Kontextová data pro doplnění do zprávy
     * @throws LoggerInvalidArgumentException Pokud první argument není jednou z konstant třídy Psr\Log\LogLevel
     */
    public function log($level, $message, array $context = array()): void
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
                $this->emergency($message, $context);
                break;
            case LogLevel::ALERT:
                $this->alert($message, $context);
                break;
            case LogLevel::CRITICAL:
                $this->critical($message, $context);
                break;
            case LogLevel::ERROR:
                $this->error($message, $context);
                break;
            case LogLevel::WARNING:
                $this->warning($message, $context);
                break;
            case LogLevel::NOTICE:
                $this->notice($message, $context);
                break;
            case LogLevel::INFO:
                $this->info($message, $context);
                break;
            case LogLevel::DEBUG:
                $this->debug($message, $context);
                break;
            default:
                throw new LoggerInvalidArgumentException('Log level not recognized. Make sure you are using one of the constants of Psr\Log\LogLevel class');
        }
    }
    
    /**
     * Systém je nepoužitelný
     * @param string $message Zpráva k zalogování
     * @param array $context Kontextové pole pro doplnění proměnných do zprávy
     * @see LoggerInterface::emergency()
     */
    public function emergency($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('EMERGENCY ', $message);
        file_put_contents(self::EMERGENCY_LOG_FILE, $finalMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Okamžitě musí být provedena akce
     * @param string $message Zpráva k zalogování
     * @param array $context Kontextové pole pro doplnění proměnných do zprávy
     * @see LoggerInterface::alert()
     */
    public function alert($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('ALERT     ', $message);
        file_put_contents(self::ALERT_LOG_FILE, $finalMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Kritická situace
     * @param string $message Zpráva k zalogování
     * @param array $context Kontextové pole pro doplnění proměnných do zprávy
     * @see LoggerInterface::critical()
     */
    public function critical($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('CRITICAL  ', $message);
        file_put_contents(self::CRITICAL_LOG_FILE, $finalMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Chyba, která nevyžaduje bezprostřední akci
     * @param string $message Zpráva k zalogování
     * @param array $context Kontextové pole pro doplnění proměnných do zprávy
     * @see LoggerInterface::ërror()
     */
    public function error($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('ERROR     ', $message);
        file_put_contents(self::ERROR_LOG_FILE, $finalMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Vyjímečné situace, které nelze považovat za chyby
     * @param string $message Zpráva k zalogování
     * @param array $context Kontextové pole pro doplnění proměnných do zprávy
     * @see LoggerInterface::warning()
     */
    public function warning($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('WARNING   ', $message);
        file_put_contents(self::WARNING_LOG_FILE, $finalMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Běžné, avšak zajímavé události
     * @param string $message Zpráva k zalogování
     * @param array $context Kontextové pole pro doplnění proměnných do zprávy
     * @see LoggerInterface::notice()
     */
    public function notice($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('NOTICE    ', $message);
        file_put_contents(self::NOTICE_LOG_FILE, $finalMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Normální události
     * @param string $message Zpráva k zalogování
     * @param array $context Kontextové pole pro doplnění proměnných do zprávy
     * @see LoggerInterface::alert()
     */
    public function info($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('INFO      ', $message);
        file_put_contents(self::INFO_LOG_FILE, $finalMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Informace využívané při vyvíjení a opravování systému, které jsou zapisovány do samostatného souboru.
     * @param mixed $message Zpráva k zalogování, může se jednat i o objekt nebo pole, v takovém případě je zalogován
     *     výstup funkce print_r($message)
     * @param array $context Kontextové pole pro doplnění proměnných do zprávy
     */
    public function debug($message, array $context = array())
    {
        if (gettype($message) === 'array' || gettype($message) === 'object') {
            ob_start();
            print_r($message);
            $finalMessage = $this->constructMessage('', ob_get_contents());
            ob_end_clean();
        } else {
            $message = $this->fillInContext($message, $context);
            $finalMessage = $this->constructMessage('DEBUG     ', $message);
        }
        file_put_contents(self::DEBUG_LOG_FILE, $finalMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Metoda doplňující do zprávy informace z kontextového pole
     * @param string $message Zpráva obsahující placeholery pro data obsažené v kontextovém poli
     * @param array $context Kontextové pole obsahující data pod stejnými klíči, jaké jsou použity v placeholderech pro
     *     ně
     * @return string Vyplněná zpráva
     */
    private function fillInContext(string $message, array $context): string
    {
        foreach ($context as $key => $val) {
            if ($val instanceof Exception) {
                $val = $val->getMessage();
            }
            $message = str_replace('{'.$key.'}', $val, $message);
        }
        return $message;
    }
    
    /**
     * Metoda skládající dohromady aktuální čas a jednotlivé části zprávy poskytnuty v argumentech
     * @param $prefix string Předpona zprávy, například typ zprávy (info, warning, error...), nepovinné
     * @param $message string Hlavní část zprávy nepovinné
     * @param string $suffix Přípona zprávy, základně znak konce řádky, nepovinné
     * @return string Řetězec vzniklý poskládáním jednotlivých částí zprávy
     */
    private function constructMessage(string $prefix = '', string $message = '', string $suffix = PHP_EOL): string
    {
        $date = (new DateTime())->format(self::TIME_FORMAT);
        return '['.$date.'] '.$prefix.$message.$suffix;
    }
}

