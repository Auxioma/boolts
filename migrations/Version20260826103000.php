<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260826103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Repair paid agency subscription periods and close replaced free subscriptions.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
UPDATE agency_subscription subscription
INNER JOIN subscription_plan plan ON plan.id = subscription.plan_id
LEFT JOIN subscription_plan_price price ON price.id = subscription.plan_price_id
LEFT JOIN (
    SELECT payment.subscription_id, MIN(payment.paid_at) AS paid_at
    FROM payment
    WHERE payment.type = 'subscription_initial'
        AND payment.paid_at IS NOT NULL
    GROUP BY payment.subscription_id
) initial_payment ON initial_payment.subscription_id = subscription.id
SET subscription.started_at = initial_payment.paid_at,
    subscription.current_period_start = initial_payment.paid_at,
    subscription.current_period_end = CASE
        WHEN price.billing_period = 'annual'
            THEN DATE_SUB(DATE_ADD(initial_payment.paid_at, INTERVAL 1 YEAR), INTERVAL 1 SECOND)
        ELSE DATE_SUB(DATE_ADD(initial_payment.paid_at, INTERVAL 1 MONTH), INTERVAL 1 SECOND)
    END
WHERE subscription.status IN ('active', 'past_due', 'incomplete')
    AND plan.is_free = 0
    AND subscription.started_at <= '1971-01-01 00:00:00'
    AND initial_payment.paid_at IS NOT NULL
SQL);

        $this->addSql(<<<'SQL'
UPDATE agency_subscription_period period
INNER JOIN agency_subscription subscription ON subscription.id = period.subscription_id
INNER JOIN subscription_plan plan ON plan.id = subscription.plan_id
LEFT JOIN subscription_plan_price price ON price.id = subscription.plan_price_id
SET period.period_start = subscription.current_period_start,
    period.period_end = subscription.current_period_end
WHERE subscription.status IN ('active', 'past_due', 'incomplete')
    AND plan.is_free = 0
    AND subscription.current_period_start > '1971-01-01 00:00:00'
    AND period.period_start <= '1971-01-01 00:00:00'
    AND price.id IS NOT NULL
SQL);

        $this->addSql(<<<'SQL'
INSERT INTO booster_transaction (
    agency_id,
    quantity,
    type,
    expires_at,
    idempotency_key,
    description,
    created_at,
    updated_at,
    subscription_period_id,
    payment_id
)
SELECT subscription.agency_id,
    period.included_boosts,
    'subscription_credit',
    period.period_end,
    CONCAT('subscription-credit-repair-', subscription.id, '-', period.id),
    'Boosts inclus dans l’abonnement.',
    NOW(),
    NOW(),
    period.id,
    initial_payment.payment_id
FROM agency_subscription_period period
INNER JOIN agency_subscription subscription ON subscription.id = period.subscription_id
INNER JOIN subscription_plan plan ON plan.id = subscription.plan_id
LEFT JOIN (
    SELECT payment.subscription_id, MIN(payment.id) AS payment_id
    FROM payment
    WHERE payment.type = 'subscription_initial'
    GROUP BY payment.subscription_id
) initial_payment ON initial_payment.subscription_id = subscription.id
LEFT JOIN booster_transaction existing_transaction
    ON existing_transaction.subscription_period_id = period.id
    AND existing_transaction.type = 'subscription_credit'
WHERE period.status = 'paid'
    AND period.included_boosts > 0
    AND plan.is_free = 0
    AND existing_transaction.id IS NULL
SQL);

        $this->addSql(<<<'SQL'
UPDATE agency_subscription free_subscription
INNER JOIN subscription_plan free_plan ON free_plan.id = free_subscription.plan_id
INNER JOIN (
    SELECT paid_subscription.agency_id, MIN(paid_subscription.started_at) AS paid_started_at
    FROM agency_subscription paid_subscription
    INNER JOIN subscription_plan paid_plan ON paid_plan.id = paid_subscription.plan_id
    WHERE paid_subscription.status IN ('active', 'past_due', 'incomplete')
        AND paid_plan.is_free = 0
        AND paid_subscription.started_at > '1971-01-01 00:00:00'
    GROUP BY paid_subscription.agency_id
) paid ON paid.agency_id = free_subscription.agency_id
SET free_subscription.status = 'canceled',
    free_subscription.cancel_at_period_end = 0,
    free_subscription.canceled_at = paid.paid_started_at,
    free_subscription.ended_at = paid.paid_started_at,
    free_subscription.current_period_end = CASE
        WHEN free_subscription.current_period_end IS NULL
            OR free_subscription.current_period_end > paid.paid_started_at
            THEN paid.paid_started_at
        ELSE free_subscription.current_period_end
    END
WHERE free_subscription.status = 'free'
    AND free_plan.is_free = 1
    AND free_subscription.ended_at IS NULL
SQL);

        $this->addSql(<<<'SQL'
UPDATE agency_subscription_period period
INNER JOIN agency_subscription subscription ON subscription.id = period.subscription_id
INNER JOIN subscription_plan plan ON plan.id = subscription.plan_id
SET period.status = 'canceled',
    period.period_end = CASE
        WHEN period.period_start <= subscription.ended_at
            AND period.period_end > subscription.ended_at
            THEN subscription.ended_at
        ELSE period.period_end
    END
WHERE period.status = 'free'
    AND plan.is_free = 1
    AND subscription.ended_at IS NOT NULL
    AND period.period_end >= subscription.ended_at
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('-- Data repair is not safely reversible.');
    }
}
