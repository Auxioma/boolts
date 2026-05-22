<?php

namespace App\Entity\Enum;

enum PerformanceEnergetique: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case E = 'E';
    case F = 'F';
    case G = 'G';

    case NON_RENSEIGNE = 'non_renseigne';
    case NON_SOUMIS = 'non_soumis';
    case EN_COURS = 'en_cours';

    public function label(): string
    {
        return match ($this) {
            self::A => 'A',
            self::B => 'B',
            self::C => 'C',
            self::D => 'D',
            self::E => 'E',
            self::F => 'F',
            self::G => 'G',

            self::NON_RENSEIGNE => 'Non renseigné',
            self::NON_SOUMIS => 'Non soumis',
            self::EN_COURS => 'En cours',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::A => 'A - ≤ 70 kWh/m²/an',
            self::B => 'B - 71 à 110 kWh/m²/an',
            self::C => 'C - 111 à 180 kWh/m²/an',
            self::D => 'D - 181 à 250 kWh/m²/an',
            self::E => 'E - 251 à 330 kWh/m²/an',
            self::F => 'F - 331 à 420 kWh/m²/an',
            self::G => 'G - > 420 kWh/m²/an',

            self::NON_RENSEIGNE => 'Non renseigné',
            self::NON_SOUMIS => 'Non soumis',
            self::EN_COURS => 'En cours',
        };
    }

    public function consommation(): string
    {
        return match ($this) {
            self::A => '≤ 70 kWh/m²/an',
            self::B => '71 à 110 kWh/m²/an',
            self::C => '111 à 180 kWh/m²/an',
            self::D => '181 à 250 kWh/m²/an',
            self::E => '251 à 330 kWh/m²/an',
            self::F => '331 à 420 kWh/m²/an',
            self::G => '> 420 kWh/m²/an',

            self::NON_RENSEIGNE,
            self::NON_SOUMIS,
            self::EN_COURS => 'Non applicable',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::A => '#009E60',
            self::B => '#52B848',
            self::C => '#C8D400',
            self::D => '#F6E300',
            self::E => '#F9B000',
            self::F => '#ED6B00',
            self::G => '#E30613',

            self::NON_RENSEIGNE => '#6c757d',
            self::NON_SOUMIS => '#343a40',
            self::EN_COURS => '#0d6efd',
        };
    }

    public function bootstrapClass(): string
    {
        return match ($this) {
            self::A,
            self::B,
            self::C => 'success',

            self::D,
            self::E => 'warning',

            self::F,
            self::G => 'danger',

            self::NON_RENSEIGNE => 'secondary',
            self::NON_SOUMIS => 'dark',
            self::EN_COURS => 'primary',
        };
    }

    public function isPassoireThermique(): bool
    {
        return in_array($this, [self::F, self::G], true);
    }

    public function isOfficialDpe(): bool
    {
        return in_array($this, [
            self::A,
            self::B,
            self::C,
            self::D,
            self::E,
            self::F,
            self::G,
        ], true);
    }

    public static function choices(bool $withTechnicalValues = true): array
    {
        $choices = [];

        foreach (self::cases() as $case) {
            if (!$withTechnicalValues && !$case->isOfficialDpe()) {
                continue;
            }

            $choices[$case->description()] = $case;
        }

        return $choices;
    }
}
