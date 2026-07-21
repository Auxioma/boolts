<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260721110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la périodicité mensuelle ou annuelle aux tarifs des forfaits.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE subscription_plan_price ADD billing_period VARCHAR(10) NOT NULL DEFAULT 'monthly'");
        $this->addSql('DROP INDEX uniq_subscription_plan_currency ON subscription_plan_price');
        $this->addSql('CREATE UNIQUE INDEX uniq_subscription_plan_currency_period ON subscription_plan_price (plan_id, currency_id, billing_period)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_subscription_plan_currency_period ON subscription_plan_price');
        $this->addSql('ALTER TABLE subscription_plan_price DROP billing_period');
        $this->addSql('CREATE UNIQUE INDEX uniq_subscription_plan_currency ON subscription_plan_price (plan_id, currency_id)');
    }
}
