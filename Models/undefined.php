<?php
namespace Poznavacky\Models;

/**
 * Datový typ označující proměnnou nebo vlastnost, do které zatím nebyla přiřazena žádná hodnota
 * Rozdíl mezi undefined a null je, že v některých případech může být do proměnné přiřazeno null jako platná hodnota,
 * zatímco undefined označuje nenačtenou hodnotu
 * @author Jan Štěch
 */
final class undefined
{
    public function __toString(): string
    {
        return 'undefined';
    }
}

