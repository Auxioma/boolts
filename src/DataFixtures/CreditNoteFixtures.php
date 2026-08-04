<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Billing\CreditNote;
use App\Entity\Billing\Enum\CreditNoteStatus;
use App\Entity\Billing\Invoice;
use App\Entity\Billing\Refund;
use App\Entity\Devise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class CreditNoteFixtures extends Fixture implements DependentFixtureInterface
{
    public const CREDIT_NOTE_REFERENCE_PREFIX = 'credit_note_';
    public const MAIN_CREDIT_NOTE_REFERENCE = 'credit_note_main_booster';

    public function load(ObjectManager $manager): void
    {
        $currency = $this->currency($manager);
        $invoice = $this->getReference(BillingFixtureData::boosterInvoiceReference('main'), Invoice::class);
        $refund = $this->getReference(RefundFixtures::MAIN_REFUND_REFERENCE, Refund::class);

        $creditNote = (new CreditNote())
            ->setNumber('AV-FIXTURE-BOOST-MAIN')
            ->setInvoice($invoice)
            ->setRefund($refund)
            ->setStatus(CreditNoteStatus::ISSUED)
            ->setReason('Remboursement partiel du pack boost fixture')
            ->setSubtotalMinor(500)
            ->setTaxTotalMinor(0)
            ->setTotalMinor(500)
            ->setCurrency($currency)
            ->setSellerSnapshot(BillingFixtureData::sellerSnapshot())
            ->setCustomerSnapshot(BillingFixtureData::customerSnapshot('main'))
            ->setProviderCreditNoteId('cn_fixture_boost_main')
            ->setIssuedAt(new \DateTimeImmutable('-2 days'));

        $manager->persist($creditNote);
        $this->addReference(self::MAIN_CREDIT_NOTE_REFERENCE, $creditNote);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            InvoiceFixtures::class,
            RefundFixtures::class,
            PaysFixtures::class,
        ];
    }

    private function currency(ObjectManager $manager): Devise
    {
        $currency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$currency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les avoirs.');
        }

        return $currency;
    }
}
