<?php

declare(strict_types=1);

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

use App\Entity\Billing\AgencyBillingProfile;
use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\AgencySubscriptionPeriod;
use App\Entity\Billing\Enum\InvoiceStatus;
use App\Entity\Billing\Enum\InvoiceType;
use App\Entity\Billing\Invoice;
use App\Entity\Billing\InvoiceLine;
use App\Entity\Billing\Payment;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Repository\Billing\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Émet la facture Boolts liée à la souscription initiale d’un forfait.
 *
 * La facture est créée immédiatement (dans le flux d’achat), avec un numéro
 * « I-100001 » attribué par {@see InvoiceNumberGenerator}. Si la facture Stripe
 * correspondante a déjà été enregistrée en base (course avec le webhook
 * `invoice.paid`), aucune nouvelle facture n’est créée.
 */
final readonly class SubscriptionInvoiceIssuer
{
    /**
     * @param array<string, string> $sellerIdentity
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InvoiceRepository $invoiceRepository,
        private InvoiceNumberGenerator $numberGenerator,
        #[Autowire('%app.invoice_seller%')]
        private array $sellerIdentity,
    ) {
    }

    public function issueForInitialPurchase(
        AgencySubscription $subscription,
        AgencySubscriptionPeriod $period,
        Payment $payment,
        SubscriptionPlanPrice $planPrice,
        ?string $providerInvoiceId,
        \DateTimeImmutable $issuedAt,
    ): ?Invoice {
        if (null !== $providerInvoiceId
            && $this->invoiceRepository->findOneBy(['providerInvoiceId' => $providerInvoiceId]) instanceof Invoice
        ) {
            return null;
        }

        $agency = $subscription->getAgency();
        $billingProfile = $agency->getBillingProfile();

        if (!$billingProfile instanceof AgencyBillingProfile) {
            throw new \LogicException('Profil de facturation manquant pour émettre la facture.');
        }

        $amountMinor = $planPrice->getAmountMinor();
        $isAnnual = 'annual' === $planPrice->getBillingPeriod()->value;

        $invoice = (new Invoice())
            ->setNumber($this->numberGenerator->next())
            ->setAgency($agency)
            ->setBillingProfile($billingProfile)
            ->setSubscription($subscription)
            ->setSubscriptionPeriod($period)
            ->setPayment($payment)
            ->setStatus(InvoiceStatus::PAID)
            ->setType(InvoiceType::SUBSCRIPTION)
            ->setCurrency($planPrice->getCurrency())
            ->setSubtotalMinor($amountMinor)
            ->setDiscountTotalMinor(0)
            ->setTaxableTotalMinor($amountMinor)
            ->setTaxTotalMinor(0)
            ->setTotalMinor($amountMinor)
            ->setAmountPaidMinor($amountMinor)
            ->setAmountDueMinor(0)
            ->setAmountRefundedMinor(0)
            ->setSellerSnapshot($this->sellerSnapshot())
            ->setCustomerSnapshot($this->customerSnapshot($subscription))
            ->setTaxSnapshot([])
            ->setProviderInvoiceId($providerInvoiceId)
            ->setIssuedAt($issuedAt)
            ->setPaidAt($issuedAt);

        $line = (new InvoiceLine())
            ->setInvoice($invoice)
            ->setType('subscription')
            ->setDescription(\sprintf(
                'Abonnement %s — %s',
                $planPrice->getPlan()->getName(),
                $isAnnual ? 'annuel' : 'mensuel',
            ))
            ->setQuantity('1.000')
            ->setUnitAmountMinor($amountMinor)
            ->setSubtotalMinor($amountMinor)
            ->setDiscountAmountMinor(0)
            ->setTaxableAmountMinor($amountMinor)
            ->setTaxAmountMinor(0)
            ->setTotalMinor($amountMinor)
            ->setPeriodStart($period->getPeriodStart())
            ->setPeriodEnd($period->getPeriodEnd())
            ->setPosition(0);

        $this->entityManager->persist($invoice);
        $this->entityManager->persist($line);

        return $invoice;
    }

    /**
     * @return array<string, string>
     */
    private function sellerSnapshot(): array
    {
        return array_filter(
            $this->sellerIdentity,
            static fn (mixed $value): bool => \is_string($value) && '' !== $value,
        );
    }

    /**
     * @return array<string, int|string|null>
     */
    private function customerSnapshot(AgencySubscription $subscription): array
    {
        $agency = $subscription->getAgency();
        $billingProfile = $agency->getBillingProfile();

        return array_filter([
            'agency_id' => $agency->getId(),
            'legal_name' => $billingProfile?->getLegalName() ?? $agency->getEntreprise(),
            'commercial_name' => $billingProfile?->getCommercialName(),
            'contact_name' => trim(\sprintf('%s %s', (string) $agency->getPrenom(), (string) $agency->getNom())),
            'email' => $billingProfile?->getBillingEmail() ?? $agency->getEmail(),
            'address_line1' => $billingProfile?->getAddressLine1(),
            'address_line2' => $billingProfile?->getAddressLine2(),
            'postal_code' => $billingProfile?->getPostalCode() ?? $agency->getCodePostal(),
            'city' => $billingProfile?->getCity(),
            'region' => $billingProfile?->getRegion(),
            'country_code' => $billingProfile?->getCountryCode(),
        ], static fn (mixed $value): bool => null !== $value && '' !== $value);
    }
}
