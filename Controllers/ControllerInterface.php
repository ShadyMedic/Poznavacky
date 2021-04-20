<?php
namespace Poznavacky\Controllers;

/**
 * Obecné rozhraní kontroleru pro MVC architekturu
 * Všechny kontrolery musejí implementovat toto rozhraní
 * @author Jan Štěch
 */
interface ControllerInterface
{
    
    /**
     * Metoda zpracovávající parametry z URL adresy
     * @param array $parameters Paremetry ke zpracování kontrolerem jako pole
     */
    function process(array $parameters): void;
    
}

