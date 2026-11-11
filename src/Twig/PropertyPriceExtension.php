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

use App\Entity\Property;
use App\Entity\PropertyPrixDevise;
use App\Repository\PropertyPrixDeviseRepository;
use App\Service\Intl\VisitorCurrencyResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Montants d'un bien dans la devise locale du visiteur (pages publiques).
 *
 *   {% set price = property_price(property) %}
 *   {{ price.prix|number_format(0, ',', ' ') }} {{ price.signe }}
 *
 * Utilise les montants convertis de `property_prix_devise`. S'il n'y a pas de
 * devise visiteur ou pas de conversion pour ce bien, renvoie les montants saisis
 * par l'agence avec la devise du bien.
 */
final class PropertyPriceExtension extends AbstractExtension
{
    /** @var array<int, array{prix: ?float, loyer: ?float, charges: ?float, depot: ?float, signe: string}> */
    private array $memo = [];

    public function __construct(
        private readonly VisitorCurrencyResolver $visitorCurrencyResolver,
        private readonly PropertyPrixDeviseRepository $propertyPrixDeviseRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('property_price', $this->propertyPrice(...)),
        ];
    }

    /**
     * @return array{prix: ?float, loyer: ?float, charges: ?float, depot: ?float, signe: string}
     */
    public function propertyPrice(Property $property): array
    {
        $key = $property->getId() ?? spl_object_id($property);

        if (isset($this->memo[$key])) {
            return $this->memo[$key];
        }

        $visitorDevise = $this->visitorCurrencyResolver->resolve();
        $sourceDevise = $property->getDevise() ?? $property->getUser()?->getDevise();

        $converted = null;

        if (null !== $visitorDevise && $visitorDevise !== $sourceDevise && null !== $property->getId()) {
            $converted = $this->propertyPrixDeviseRepository->findOneBy([
                'property' => $property,
                'devise' => $visitorDevise,
            ]);
        }

        if ($converted instanceof PropertyPrixDevise) {
            return $this->memo[$key] = [
                'prix' => $this->toNumber($converted->getPrix()),
                'loyer' => $this->toNumber($converted->getMontantLoyerHorsCharge()),
                'charges' => $this->toNumber($converted->getMontantDesCharges()),
                'depot' => $this->toNumber($converted->getMontantDepotDeGarantie()),
                'signe' => $visitorDevise->getSigne() ?? '€',
            ];
        }

        return $this->memo[$key] = [
            'prix' => $this->toNumber($property->getPrix()),
            'loyer' => $this->toNumber($property->getMontantLoyerHorsCharge()),
            'charges' => $this->toNumber($property->getMontantDesCharges()),
            'depot' => $this->toNumber($property->getMontantDepotDeGarantie()),
            'signe' => $sourceDevise?->getSigne() ?? '€',
        ];
    }

    /**
     * Charges et dépôt de garantie sont encore en VARCHAR : on n'accepte qu'une valeur numérique.
     */
    private function toNumber(?string $amount): ?float
    {
        if (null === $amount || '' === $amount) {
            return null;
        }

        $normalized = str_replace([' ', "\u{00A0}", "\u{202F}", ','], ['', '', '', '.'], $amount);

        return is_numeric($normalized) ? (float) $normalized : null;
    }
}
