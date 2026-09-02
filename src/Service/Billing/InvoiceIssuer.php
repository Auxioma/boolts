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
use App\Entity\Booster\BoosterPackPrice;
use App\Entity\Devise;
use App\Entity\User;
use App\Repository\Billing\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Émet les factures Boolts (abonnement initial, pack boost) au fil des achats.
 *
 * Le numéro « I-100001 » est attribué par {@see InvoiceNumberGenerator}. La
 * facture et sa ligne sont persistées mais pas flushées : c’est l’appelant qui
 * décide du moment du flush (généralement dans la même transaction que le
 * paiement).
 */
final readonly class InvoiceIssuer
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
        $amountMinor = $planPrice->getAmountMinor();
        $isAnnual = 'annual' === $planPrice->getBillingPeriod()->value;

        $invoice = $this->newInvoice($agency, $payment, InvoiceType::SUBSCRIPTION, $planPrice->getCurrency(), $amountMinor, $issuedAt)
            ->setSubscription($subscription)
            ->setSubscriptionPeriod($period)
            ->setProviderInvoiceId($providerInvoiceId);

        $line = $this->newLine(
            $invoice,
            'subscription',
            \sprintf(
                'Abonnement %s — %s',
                $planPrice->getPlan()->getName(),
                $isAnnual ? 'annuel' : 'mensuel',
            ),
            $amountMinor,
        )
            ->setPeriodStart($period->getPeriodStart())
            ->setPeriodEnd($period->getPeriodEnd());

        $this->entityManager->persist($invoice);
        $this->entityManager->persist($line);

        return $invoice;
    }

    public function issueForBoosterPack(
        User $agency,
        Payment $payment,
        BoosterPackPrice $boostPrice,
        \DateTimeImmutable $issuedAt,
    ): Invoice {
        $amountMinor = $boostPrice->getAmountMinor();
        $pack = $boostPrice->getBoosterPack();
        $quantity = $pack->getBoostQuantity();

        $invoice = $this->newInvoice(
            $agency,
            $payment,
            InvoiceType::BOOSTER_PACK,
            $boostPrice->getCurrency(),
            $amountMinor,
            $issuedAt,
        );

        $line = $this->newLine(
            $invoice,
            'booster_pack',
            \sprintf(
                'Pack boost %s (%d boost%s)',
                $pack->getName(),
                $quantity,
                $quantity > 1 ? 's' : '',
            ),
            $amountMinor,
        );

        $this->entityManager->persist($invoice);
        $this->entityManager->persist($line);

        return $invoice;
    }

    private function newInvoice(
        User $agency,
        Payment $payment,
        InvoiceType $type,
        Devise $currency,
        int $amountMinor,
        \DateTimeImmutable $issuedAt,
    ): Invoice {
        $billingProfile = $agency->getBillingProfile();

        if (!$billingProfile instanceof AgencyBillingProfile) {
            throw new \LogicException('Profil de facturation manquant pour émettre la facture.');
        }

        return (new Invoice())
            ->setNumber($this->numberGenerator->next())
            ->setAgency($agency)
            ->setBillingProfile($billingProfile)
            ->setPayment($payment)
            ->setStatus(InvoiceStatus::PAID)
            ->setType($type)
            ->setCurrency($currency)
            ->setSubtotalMinor($amountMinor)
            ->setDiscountTotalMinor(0)
            ->setTaxableTotalMinor($amountMinor)
            ->setTaxTotalMinor(0)
            ->setTotalMinor($amountMinor)
            ->setAmountPaidMinor($amountMinor)
            ->setAmountDueMinor(0)
            ->setAmountRefundedMinor(0)
            ->setSellerSnapshot($this->sellerSnapshot())
            ->setCustomerSnapshot($this->customerSnapshot($agency))
            ->setTaxSnapshot([])
            ->setIssuedAt($issuedAt)
            ->setPaidAt($issuedAt);
    }

    private function newLine(Invoice $invoice, string $type, string $description, int $amountMinor): InvoiceLine
    {
        return (new InvoiceLine())
            ->setInvoice($invoice)
            ->setType($type)
            ->setDescription($description)
            ->setQuantity('1.000')
            ->setUnitAmountMinor($amountMinor)
            ->setSubtotalMinor($amountMinor)
            ->setDiscountAmountMinor(0)
            ->setTaxableAmountMinor($amountMinor)
            ->setTaxAmountMinor(0)
            ->setTotalMinor($amountMinor)
            ->setPosition(0);
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
    private function customerSnapshot(User $agency): array
    {
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
