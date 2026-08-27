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

namespace App\Tests\Architecture;

use App\Entity\Billing\Payment;
use App\Entity\Billing\PaymentAttempt;
use App\Entity\Billing\SubscriptionEmailLog;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\TestCase;

final class SubscriptionIdempotencyMappingTest extends TestCase
{
    public function testStripeInvoiceAndPaymentIntentAreUniqueOnPayment(): void
    {
        self::assertColumnIsUnique(Payment::class, 'providerInvoiceId');
        self::assertColumnIsUnique(Payment::class, 'providerPaymentIntentId');
    }

    public function testPaymentAttemptIsUniqueForInvoiceAndAttemptNumber(): void
    {
        self::assertClassHasUniqueConstraint(
            PaymentAttempt::class,
            'uniq_payment_attempt_invoice_number',
            ['subscription_id', 'provider_invoice_id', 'attempt_number'],
        );
    }

    public function testSubscriptionEmailIsUniqueForBusinessEvent(): void
    {
        self::assertClassHasUniqueConstraint(
            SubscriptionEmailLog::class,
            'uniq_subscription_email_event',
            ['subscription_id', 'event_type', 'event_key'],
        );
    }

    /**
     * @param class-string $entityClass
     */
    private static function assertColumnIsUnique(string $entityClass, string $propertyName): void
    {
        $property = (new \ReflectionClass($entityClass))->getProperty($propertyName);
        $attributes = $property->getAttributes(ORM\Column::class);

        self::assertNotEmpty($attributes, \sprintf('%s::$%s doit être une colonne Doctrine.', $entityClass, $propertyName));

        $column = $attributes[0]->newInstance();

        self::assertTrue(
            $column->unique,
            \sprintf('%s::$%s doit porter une contrainte UNIQUE.', $entityClass, $propertyName),
        );
    }

    /**
     * @param class-string $entityClass
     * @param list<string> $expectedColumns
     */
    private static function assertClassHasUniqueConstraint(
        string $entityClass,
        string $name,
        array $expectedColumns,
    ): void {
        $constraints = (new \ReflectionClass($entityClass))->getAttributes(ORM\UniqueConstraint::class);

        foreach ($constraints as $constraintAttribute) {
            $constraint = $constraintAttribute->newInstance();

            if ($constraint->name === $name) {
                self::assertSame($expectedColumns, $constraint->columns);

                return;
            }
        }

        self::fail(\sprintf('%s doit déclarer la contrainte UNIQUE %s.', $entityClass, $name));
    }
}
