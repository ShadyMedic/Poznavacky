<?php

namespace Poznavacky\Models;

use Psr\Log\InvalidArgumentException as LoggerInvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use \DateTime;

/**
 * Třída sloužící k zaznamenání všech příchozích akcí při přijetí HTTP požadavku
 * @author Jan Štěch
 */
class Logger implements LoggerInterface
{
    private const LOG_FILE = 'poznavacky.log';
    private const TIME_FORMAT = 'Y-m-d H:i:s';

    private $handle;
    private $oneUse;

    /**
     * Konstruktor třídy otevírající logovací soubor pro připojování dalšího zápisu na konec
     * @param bool $isOneUse TRUE, pokud má být po zalogování první zprávy automaticky logovací soubor zavřen a tato instance tak znepoužitelněna
     */
    public function __construct(bool $isOneUse)
    {
        $this->handle = fopen(self::LOG_FILE, 'a');
        $this->oneUse = $isOneUse;
    }

    /**
     * Destruktor třídy zavírající logovací soubor a umožňující tak jeho používání jinými procesy
     */
    public function __destruct()
    {
        if (get_resource_type($this->handle) === 'stream'){ fclose($this->handle); }
    }

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
     * @see LoggerInterface::emergency()
     */
    public function emergency($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('EMERGENCY ', $message);
        $this->writeMessage($finalMessage);
    }

    /**
     * Okamžitě musí být provedena akce
     * @see LoggerInterface::alert()
     */
    public function alert($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('ALERT     ', $message);
        $this->writeMessage($finalMessage);
    }

    /**
     * Kritická situace
     * @see LoggerInterface::critical()
     */
    public function critical($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('CRITICAL  ', $message);
        $this->writeMessage($finalMessage);
    }

    /**
     * Chyba, která nevyžaduje bezprostřední akci
     * @see LoggerInterface::ërror()
     */
    public function error($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('ERROR     ', $message);
        $this->writeMessage($finalMessage);
    }

    /**
     * Vyjímečné situace, které nelze považovat za chyby
     * @see LoggerInterface::warning()
     */
    public function warning($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('WARNING   ', $message);
        $this->writeMessage($finalMessage);
    }

    /**
     * Běžné, avšak zajímavé události
     * @see LoggerInterface::notice()
     */
    public function notice($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('NOTICE    ', $message);
        $this->writeMessage($finalMessage);
    }

    /**
     * Normální události
     * @see LoggerInterface::alert()
     */
    public function info($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('INFO      ', $message);
        $this->writeMessage($finalMessage);
    }

    /**
     * Informace využívané při vyvíjení a opravování systému, které jsou zapisovány do samostatného souboru.
     */
    public function debug($message, array $context = array())
    {
        $debugHandle = fopen('debug.log', 'a');
        if (gettype($message) === 'array' || gettype($message) === 'object')
        {
            ob_start();
            print_r($message);
            $finalMessage = $this->constructMessage('', ob_get_contents());
            ob_end_clean();
        }
        else
        {
            $message = $this->fillInContext($message, $context);
            $finalMessage = $this->constructMessage('', $message);
        }
        fwrite($debugHandle, $finalMessage);
        fclose($debugHandle);
    }

    /**
     * Metoda doplňující do zprávy informace z kontextového pole
     * @param string $message Zpráva obsahující placeholery pro data obsažené v kontextovém poli
     * @param array $context Kontextové pole obsahující data pod stejnými klíči, jaké jsou použity v placeholderech pro ně
     * @return string Vyplněná zpráva
     */
    private function fillInContext(string $message, array $context): string
    {
        foreach ($context as $key => $val)
        {
            if ($val instanceof Exception) { $val = $val->getMessage(); }
            $message = str_replace('{' . $key . '}', $val, $message);
        }
        return $message;
    }

    /**
     * Metoda skládající dohromady aktuální čas a jednotlivé části zprávy poskytnuty v argumentech
     * @param $prefix Předpona zprávy, například typ zprávy (info, warning, error...), nepovinné
     * @param $message Hlavní část zprávy
     * @param string $suffix Přípona zprávy, základně znak konce řádky, nepovinné
     * @return string Řetězec vzniklý poskládáním jednotlivých částí zprávy
     */
    private function constructMessage($prefix = '', $message, $suffix = PHP_EOL): string
    {
        $date = (new DateTime())->format(self::TIME_FORMAT);
        return '[' . $date . '] ' . $prefix . $message . $suffix;
    }

    /**
     * Metoda zapisující finální poskládaný řetězec do logovacího souboru bez jakýchkoliv dalších úprav
     * Pokud je tato instance nastavena jako na jedno použití, je po zapsání zprávy soubor zavřen
     * @param string $text Řetězec k zapsání
     */
    private function writeMessage(string $text): void
    {
        fwrite($this->handle, $text);
        if ($this->oneUse) { $this->__destruct(); }
    }
}

