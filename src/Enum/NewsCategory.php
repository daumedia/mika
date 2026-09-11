<?php

namespace App\Enum;

/**
 * Themekategorie einer Neuigkeit. Der Backing-Value (Kleinschreibung, Englisch)
 * ist zugleich der Schlüsselteil der Übersetzung `home.themes.<value>.title` und
 * bleibt der in der Datenbank gespeicherte String — die vorhandenen Datensätze
 * (inclusion/youth/housing/culture) bleiben gültig, es ist keine Migration nötig.
 */
enum NewsCategory: string
{
    case Inclusion = 'inclusion';
    case Youth = 'youth';
    case Housing = 'housing';
    case Culture = 'culture';

    /** Luxemburgische Beschriftung für Formular und Admin-Übersicht. */
    public function label(): string
    {
        return match ($this) {
            self::Inclusion => 'Inklusioun',
            self::Youth => 'Jugend',
            self::Housing => 'Wunnen',
            self::Culture => 'Kultur',
        };
    }
}
