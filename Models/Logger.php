<?php

namespace Poznavacky\Models;

use Poznavacky\Models\Emails\EmailComposer;
use Poznavacky\Models\Emails\EmailSender;
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
    public const EMERGENCY_LOG_FILE = 'log/errors.log';
    public const ALERT_LOG_FILE = 'log/errors.log';
    public const CRITICAL_LOG_FILE = 'log/errors.log';
    public const ERROR_LOG_FILE = 'log/errors.log';
    public const WARNING_LOG_FILE = 'log/warnings.log';
    public const NOTICE_LOG_FILE = 'log/info.log';
    public const INFO_LOG_FILE = 'log/info.log';
    public const DEBUG_LOG_FILE = 'log/debug.log';

    private const TIME_FORMAT = 'Y-m-d H:i:s';

    private const WEBMASTER_EMAIL = 'honza.stech@gmail.com';
    
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
        $this->mailWebmaster('EMERGENCY na poznavacky.com', 'Chyba úrovně EMERGENCY', 'a80337', $finalMessage);
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
        $this->mailWebmaster('ALERT na poznavacky.com', 'Chyba úrovně ALERT', 'dd0000', $finalMessage);
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
        $this->mailWebmaster('CRITICAL na poznavacky.com', 'Chyba úrovně CRITICAL', 'ff0000', $finalMessage);
    }
    
    /**
     * Chyba, která nevyžaduje bezprostřední akci
     * @param string $message Zpráva k zalogování
     * @param array $context Kontextové pole pro doplnění proměnných do zprávy
     * @see LoggerInterface::error()
     */
    public function error($message, array $context = array())
    {
        $message = $this->fillInContext($message, $context);
        $finalMessage = $this->constructMessage('ERROR     ', $message);
        file_put_contents(self::ERROR_LOG_FILE, $finalMessage, FILE_APPEND | LOCK_EX);
        $this->mailWebmaster('ERROR na poznavacky.com', 'Chyba úrovně ERROR', 'fc7005', $finalMessage);
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

    /**
     * Metoda odesílající chybovou hlášku webmasterovy e-mailem
     * @param string $subject Předmět e-mailu
	 * @param string $title Nadpis e-mailu
	 * @param string $barColor Barva pro barevný proužek pod nadpisem (viz e-mailový pohled errorReport.phtml)
     * @param string $message Obsah zprávy
     * @return bool TRUE, pokud se e-mail podařilo odeslat, FALSE, pokud ne
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function mailWebmaster(string $subject, string $title, string $barColor, string $message) : bool
    {
        $composer = new EmailComposer();
        $sender = new EmailSender();

        $composer->composeMail(EmailComposer::EMAIL_TYPE_ERROR_REPORT, array(
			'title' => $title,
			'barColor' => $barColor,
            'content' => $message,
        ));
        return $sender->sendMail(self::WEBMASTER_EMAIL, $subject, $composer->getMail(), 'poznavacky@email.com', 'Poznávačky', true, false);
    }
}

