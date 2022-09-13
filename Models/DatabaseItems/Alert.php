<?php

namespace Poznavacky\Models\DatabaseItems;

use DateTime;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\undefined;

/**
 * Třída reprezentující objekt chybového hlášení naimportovaný z logovacího souboru do databáze
 * @author Jan Štěch
 */
class Alert extends DatabaseItem
{

    public const TABLE_NAME = 'chyby';

    public const COLUMN_DICTIONARY = array(
        'id' => 'chyby_id',
        'time' => 'cas',
        'level' => 'uroven',
        'content' => 'obsah',
        'resolved' => 'vyreseno'
    );

    protected const NON_PRIMITIVE_PROPERTIES = array(/* Žádná z vlastností neukládá objekt */
    );

    protected const DEFAULT_VALUES = array(
        'resolved' => false,
    );

    protected const CAN_BE_CREATED = true;
    protected const CAN_BE_UPDATED = true;

    protected $time;
    protected $level;
    protected $content;
    protected $resolved;

    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * Při nastavení některého z argumentů na undefined, je hodnota dané vlastnosti také nastavena na undefined
     * Při nastavení některého z argumentů na null, není hodnota dané vlastnosti nijak pozměněna
     * @param DateTime|undefined|null $time
     * @param string|undefined|null $level
     * @param string|undefined|null $content
     * @param bool|undefined|null $resolved
     * @return void
     */
    public function initialize($time = null, $level = null, $content = null, $resolved = null): void
    {
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($time === null) {
            $time = $this->time;
        }
        if ($level === null) {
            $level = $this->level;
        }
        if ($content === null) {
            $content = $this->content;
        }
        if ($resolved === null) {
            $time = $this->resolved;
        }

        $this->time = $time;
        $this->level = $level;
        $this->content = $content;
        $this->resolved = $resolved;
    }

    /**
     * Metoda označující toto chybové hlášení jako vyřešené (pro uložení do databáze je následně nutné zavolat save())
     * @return void
     */
    public function resolve(): void
    {
        $this->resolved = true;
    }

    /**
     * Metoda navracející uložený čas vzniku tohoto chybového hlášení.
     * @return string Čas vzniku jako řetězec ve formátu YYYY-MM-DD hh:mm:ss
     * @throws DatabaseException
     */
    public function getTime() : string
    {
        $this->loadIfNotLoaded($this->time);
        return $this->time->format('Y-m-d H:i:s');
    }

    /**
     * Metoda navracející uloženou úroveň tohoto chybového hlášení
     * @return string Úroveň hlášení jako řezězec velkými písmeny (@see Logger)
     * @throws DatabaseException
     */
    public function getLevel()
    {
        $this->loadIfNotLoaded($this->level);
        return $this->level;
    }

    /**
     * Metoda navracející obsah tohoto chybového hlášení
     * @return string Plný obsah chybového hlášení
     * @throws DatabaseException
     */
    public function getContent()
    {
        $this->loadIfNotLoaded($this->content);
        return $this->content;
    }

}