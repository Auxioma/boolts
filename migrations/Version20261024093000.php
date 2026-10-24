<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Prix des forfaits en USD et CHF, convertis depuis l'EUR au taux BCE du
 * 23/10/2026 (1 € = 1,1193 $ = 0,9227 CHF), arrondis au prix en ,99
 * immédiatement supérieur. N'insère que les prix absents.
 */
final class Version20261024093000 extends AbstractMigration
{
    private const PRICES = [
        'free-monthly' => ['monthly', ['USD' => 0, 'CHF' => 0]],
        'debutant' => ['monthly', ['USD' => 2299, 'CHF' => 1899]],
        'confirme' => ['monthly', ['USD' => 5599, 'CHF' => 4699]],
        'free-annual' => ['annual', ['USD' => 0, 'CHF' => 0]],
        'started' => ['annual', ['USD' => 22399, 'CHF' => 18499]],
        'boss' => ['annual', ['USD' => 55999, 'CHF' => 46199]],
    ];

    private const CURRENCIES = [
        'USD' => 'dollar des États-Unis (USD)',
        'CHF' => 'franc suisse (CHF)',
    ];

    public function getDescription(): string
    {
        return 'Forfaits : prix en USD et CHF';
    }

    public function up(Schema $schema): void
    {
        foreach (self::PRICES as $planCode => [$billingPeriod, $amounts]) {
            foreach ($amounts as $currencyCode => $amountMinor) {
                $this->addSql(
                    'INSERT INTO subscription_plan_price (plan_id, currency_id, amount_minor, billing_period, is_active, created_at, updated_at)
                     SELECT p.id, d.id, ?, ?, 1, NOW(), NOW()
                     FROM subscription_plan p
                     INNER JOIN devise d ON d.nom = ?
                     WHERE p.code = ?
                       AND NOT EXISTS (
                           SELECT 1 FROM subscription_plan_price pp
                           WHERE pp.plan_id = p.id AND pp.currency_id = d.id AND pp.billing_period = ?
                       )',
                    [$amountMinor, $billingPeriod, self::CURRENCIES[$currencyCode], $planCode, $billingPeriod],
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        // Ne supprime que les prix jamais utilisés (aucun abonnement ne les référence).
        $this->addSql(
            'DELETE pp FROM subscription_plan_price pp
             INNER JOIN devise d ON d.id = pp.currency_id
             LEFT JOIN agency_subscription s ON s.plan_price_id = pp.id OR s.pending_plan_price_id = pp.id
             WHERE d.nom IN (?, ?) AND s.id IS NULL',
            array_values(self::CURRENCIES),
        );
    }
}
