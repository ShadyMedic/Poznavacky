<?php

namespace Poznavacky\Models\Processors;

use Poznavacky\Models\DatabaseItems\Alert;

/**
 * Třída starající se o import chybových hlášení z logovacího souboru
 * @author Jan Štěch
 */
class AlertImporter
{

    /**
     * Metoda importující nová chybová hlášení z logovacího souboru do databáze
     * @return int|null Počet naimportovaných chybových hlášení, nebo NULL v případě neúspěchu
     */
    public function importAlerts(): ?int
    {
        //TODO
    }
}