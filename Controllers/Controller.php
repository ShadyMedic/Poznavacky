<?php
namespace Poznavacky\Controllers;

/** 
 * Obecný kontroler pro MVC architekturu
 * Mateřská třída všech kontrolerů
 * @author Jan Štěch
 */
abstract class Controller
{

    /**
     * Metoda zpracovávající parametry z URL adresy
     * @param array $parameters Paremetry ke zpracování jako pole
     */
    abstract function process(array $parameters): void;

}

