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

namespace App\Service\Billing;

use App\Entity\Devise;
use App\Entity\User;
use App\Repository\Billing\SubscriptionPlanPriceRepository;
use App\Repository\DeviseRepository;

/**
 * Devise dans laquelle une agence voit et paie ses forfaits.
 *
 * C'est la devise de son profil (User::devise) si des prix de forfait
 * existent dans cette devise (aujourd'hui EUR, USD, CHF) ; sinon l'euro.
 */
final readonly class SubscriptionCurrencyResolver
{
    public const FALLBACK_CURRENCY_NAME = 'euro (EUR)';

    public function __construct(
        private SubscriptionPlanPriceRepository $subscriptionPlanPriceRepository,
        private DeviseRepository $deviseRepository,
    ) {
    }

    public function resolve(User $agency): Devise
    {
        $currency = $agency->getDevise();

        if ($currency instanceof Devise && $this->subscriptionPlanPriceRepository->hasActivePricesInCurrency($currency)) {
            return $currency;
        }

        $fallback = $this->deviseRepository->findOneBy(['nom' => self::FALLBACK_CURRENCY_NAME]);

        if (!$fallback instanceof Devise) {
            throw new \LogicException('La devise EUR est introuvable.');
        }

        return $fallback;
    }
}
