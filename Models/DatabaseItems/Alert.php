<?php

namespace Poznavacky\Models\DatabaseItems;

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
     * @inheritDoc
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

    public function resolve()
    {
        //TODO
    }

}