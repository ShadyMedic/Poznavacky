<?php

namespace Poznavacky\Models\DatabaseItems;

/**
 * Rozhraní používané modely, které je možné zobrazit v tabulce pro řešení hlášení (v čase tvorby tohoto souboru
 * to jsou modely Report a Object).
 * Rozhraní předepisuje metody, které pohled s touto tabulkou využívá.
 * @author Jan Štěch
 */
interface DisplayableInReportsTable
{
    /**
     * Metoda navracející ID položky, která má být ovlivněna akcí
     * @return int ID hlášení, nebo obrázku
     */
    public function getId(): int;

    /**
     * Metoda navracející název přírodniny na obrázku, který je předmětem položky v tabulce
     * @return string Název přírodniny
     */
    public function getNaturalName(): string;

    /**
     * Metoda navracející URL obrázku, který je předmětem položky v tabulce
     * @return string URL adresa obrázku
     */
    public function getUrl(): string;
}

