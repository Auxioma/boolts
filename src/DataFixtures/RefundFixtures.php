<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Billing\Enum\RefundReason;
use App\Entity\Billing\Enum\RefundStatus;
use App\Entity\Billing\Invoice;
use App\Entity\Billing\Payment;
use App\Entity\Billing\Refund;
use App\Entity\Devise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class RefundFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFUND_REFERENCE_PREFIX = 'refund_';
    public const MAIN_REFUND_REFERENCE = 'refund_main_booster';

    public function load(ObjectManager $manager): void
    {
        $currency = $this->currency($manager);
        $payment = $this->getReference(BillingFixtureData::boosterPaymentReference('main'), Payment::class);
        $invoice = $this->getReference(BillingFixtureData::boosterInvoiceReference('main'), Invoice::class);

        $refund = (new Refund())
            ->setReference('REF-FIXTURE-BOOST-MAIN')
            ->setPayment($payment)
            ->setInvoice($invoice)
            ->setStatus(RefundStatus::SUCCEEDED)
            ->setReason(RefundReason::REQUESTED_BY_CUSTOMER)
            ->setAmountMinor(500)
            ->setCurrency($currency)
            ->setProviderRefundId('re_fixture_boost_main')
            ->setProviderBalanceTransactionId('txn_fixture_refund_main')
            ->setMetadata(['fixture' => true, 'kind' => 'booster_refund'])
            ->setRequestedAt(new \DateTimeImmutable('-3 days'))
            ->setProcessedAt(new \DateTimeImmutable('-2 days'));

        $manager->persist($refund);
        $this->addReference(self::MAIN_REFUND_REFERENCE, $refund);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PaymentFixtures::class,
            InvoiceFixtures::class,
            PaysFixtures::class,
        ];
    }

    private function currency(ObjectManager $manager): Devise
    {
        $currency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$currency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les remboursements.');
        }

        return $currency;
    }
}
