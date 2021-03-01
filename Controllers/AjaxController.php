<?php
namespace Poznavacky\Controllers;

use Poznavacky\Controllers\ControllerInterface;

/**
 * Obecný kontroler pro zpracovávání AJAX požadavků
 * Mateřská třída všech AJAX kotntrolerů
 */
abstract class AjaxController implements ControllerInterface
{

    /**
     * Metoda ověřující, zda je požadavek, který spustil běh skriptu AJAX
     * @return bool TRUE, pokud je požadavek AJAX, FALSE, pokud ne
     */
    protected function checkIfCalledAsAjax(): bool
    {
        //TODO
    }
}

