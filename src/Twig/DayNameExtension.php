<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Jour de la semaine des horaires d'ouverture (stocké en français : "lundi"…)
 * affiché dans la langue de la page, avec une majuscule : {{ horaire.jour|day_name }}.
 */
final class DayNameExtension extends AbstractExtension
{
    /** Jour stocké → date ISO d'une semaine de référence (2024-01-01 = lundi). */
    private const REFERENCE_DATES = [
        'lundi' => '2024-01-01',
        'mardi' => '2024-01-02',
        'mercredi' => '2024-01-03',
        'jeudi' => '2024-01-04',
        'vendredi' => '2024-01-05',
        'samedi' => '2024-01-06',
        'dimanche' => '2024-01-07',
    ];

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('day_name', $this->dayName(...)),
        ];
    }

    public function dayName(?string $jour, ?string $locale = null): string
    {
        $jour = mb_trim((string) $jour);
        $date = self::REFERENCE_DATES[mb_strtolower($jour)] ?? null;
        $locale ??= $this->requestStack->getCurrentRequest()?->getLocale() ?? 'fr';

        $name = $jour;

        if (null !== $date) {
            $formatter = new \IntlDateFormatter(
                $locale,
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::NONE,
                'UTC',
                \IntlDateFormatter::GREGORIAN,
                'EEEE'
            );
            $formatted = $formatter->format(new \DateTimeImmutable($date, new \DateTimeZone('UTC')));

            if (false !== $formatted && '' !== $formatted) {
                $name = $formatted;
            }
        }

        return mb_strtoupper(mb_substr($name, 0, 1)).mb_substr($name, 1);
    }
}
