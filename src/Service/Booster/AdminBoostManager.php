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

namespace App\Service\Booster;

use App\Entity\Billing\Enum\BoosterTransactionType;
use App\Entity\Billing\Enum\PropertyBoostStatus;
use App\Entity\Booster\BoosterTransaction;
use App\Entity\Booster\PropertyBoost;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Opérations d'administration sur les boosts d'annonces.
 *
 * - annulation simple : le boost est marqué CANCELED, le débit d'origine
 *   reste tel quel (l'agence ne récupère rien) ;
 * - annulation avec recrédit : le boost est marqué CANCELED et le crédit
 *   est rendu à l'agence sur la source d'origine (forfait ou boosts achetés).
 */
final class AdminBoostManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Annule le boost sans recréditer l'agence.
     */
    public function cancel(PropertyBoost $boost, ?\DateTimeImmutable $now = null): void
    {
        if (PropertyBoostStatus::CANCELED === $boost->getStatus()) {
            return;
        }

        $this->markCanceled($boost, $now ?? new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    /**
     * Annule le boost et recrédite le boost à l'agence sur sa source d'origine.
     */
    public function cancelAndRefund(PropertyBoost $boost, ?\DateTimeImmutable $now = null): void
    {
        $now ??= new \DateTimeImmutable();

        if (PropertyBoostStatus::CANCELED !== $boost->getStatus()) {
            $this->markCanceled($boost, $now);
            $this->refund($boost, $now);
        }

        $this->entityManager->flush();
    }

    private function markCanceled(PropertyBoost $boost, \DateTimeImmutable $now): void
    {
        $boost->setStatus(PropertyBoostStatus::CANCELED);
        $boost->setCanceledAt($now);
    }

    private function refund(PropertyBoost $boost, \DateTimeImmutable $now): void
    {
        $debit = $boost->getBoosterTransaction();
        $period = $debit->getSubscriptionPeriod();

        /*
         * Boost issu du forfait, dont la période couvre encore la date du
         * jour : le solde forfait se calcule "boosts inclus − débits de la
         * période". Aucune écriture ne peut le re-créditer, on neutralise
         * donc le débit d'origine.
         */
        if (
            null !== $period
            && $period->getPeriodStart() <= $now
            && $period->getPeriodEnd() >= $now
        ) {
            $debit->setQuantity(0);
            $debit->setDescription(mb_trim(\sprintf(
                '%s — Boost annulé et recrédité par un administrateur le %s.',
                (string) $debit->getDescription(),
                $now->format('d/m/Y H:i'),
            )));

            return;
        }

        /*
         * Boost acheté (ou forfait dont la période est terminée) : écriture
         * de crédit compensatoire dans le solde "boosts achetés".
         */
        $credit = (new BoosterTransaction())
            ->setAgency($boost->getAgency())
            ->setProperty($boost->getProperty())
            ->setQuantity(1)
            ->setType(BoosterTransactionType::ADMIN_CREDIT)
            ->setBoosterPack($debit->getBoosterPack())
            ->setExpiresAt(null)
            ->setIdempotencyKey(\sprintf(
                'admin-boost-refund-%d-%s',
                (int) $boost->getId(),
                bin2hex(random_bytes(6)),
            ))
            ->setDescription(\sprintf(
                'Recrédit du boost de l’annonce #%d annulé par un administrateur le %s.',
                $boost->getProperty()->getId(),
                $now->format('d/m/Y H:i'),
            ));

        $this->entityManager->persist($credit);
    }
}
