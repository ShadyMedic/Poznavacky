<?php
namespace Poznavacky\DataGetters;

/**
 * Rozhraní pro získáváač dat
 * Toto rozhraní je implementováno všemi získávači dat
 */
interface DataGetter
{

    /**
     * Metoda získávající všechna potřebná data pro nedefinitivní pohled a navracející je jako asociativní pole
     * @return array Asociativní pole získaných hodnot
     */
    public function get(): array;
}

