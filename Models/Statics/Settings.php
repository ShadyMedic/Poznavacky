<?php

namespace Poznavacky\Models\Statics;

/**
 * Třída obsahující všechna technická nastavení pro aplikaci jako statické konstanty
 * @author Jan Štěch
 */
class Settings
{
    /**
     * Rozhoduje, zda má aplikace běžet v produkčním nebo vývojovém režimu.
     * Pokud je toto nastavení nastaveno na FALSE, množství funkcí, které na lokálním serveru nefungují
     * nebo působí problémy, jako vynucené SSL spojení, je vypnuto
     */
    public const PRODUCTION_ENVIRONMENT = false;

    /**
     * Server hostující MySQL databázi pro aplikaci
     */
    public const DB_HOST = 'localhost';

    /**
     * Uživatelské jméno pro přístup do databáze aplikace
     */
    public const DB_USERNAME = 'root';

    /**
     * Heslo pro přístup do databáze aplikace
     */
    public const DB_PASSWORD = '';

    /**
     * Jméno databáze pro aplikaci
     */
    public const DB_NAME = 'poznavacky';

    /**
     * Pokud je aplikace ve vývojovém režimu, všechny odchozí e-maily jsou odesílány na tuto e-mailovou adresu.
     * Toto zamezí spamování skutečných uživatelů
     */
    public const DEVELOPMENT_EMAIL_COLLECTOR = 'dummy@poznavacky.com';

    /* E-mail settings can be found in Poznavacky\Models\Emails\EmailSender */

}

