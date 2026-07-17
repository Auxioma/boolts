<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260716105144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE agency_billing_profile (id INT AUTO_INCREMENT NOT NULL, stripe_customer_id VARCHAR(255) NOT NULL, billing_email VARCHAR(255) DEFAULT NULL, legal_name VARCHAR(255) DEFAULT NULL, commercial_name VARCHAR(255) DEFAULT NULL, address_line1 VARCHAR(255) DEFAULT NULL, address_line2 VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(30) DEFAULT NULL, city VARCHAR(150) DEFAULT NULL, region VARCHAR(150) DEFAULT NULL, country_code VARCHAR(2) DEFAULT NULL, locale VARCHAR(10) DEFAULT \'fr\' NOT NULL, tax_exempt_status VARCHAR(30) DEFAULT \'none\' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, agency_id INT NOT NULL, preferred_currency_id INT DEFAULT NULL, default_payment_method_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_7F37F400708DC647 (stripe_customer_id), UNIQUE INDEX UNIQ_7F37F400CDEADB2A (agency_id), INDEX IDX_7F37F4007A5B1307 (preferred_currency_id), UNIQUE INDEX UNIQ_7F37F400AF212FD0 (default_payment_method_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE agency_payment_method (id INT AUTO_INCREMENT NOT NULL, stripe_payment_method_id VARCHAR(255) NOT NULL, stripe_setup_intent_id VARCHAR(255) DEFAULT NULL, stripe_mandate_id VARCHAR(255) DEFAULT NULL, type VARCHAR(30) NOT NULL, brand VARCHAR(50) DEFAULT NULL, last4 VARCHAR(4) DEFAULT NULL, exp_month INT DEFAULT NULL, exp_year INT DEFAULT NULL, cardholder_name VARCHAR(255) DEFAULT NULL, country_code VARCHAR(2) DEFAULT NULL, funding VARCHAR(30) DEFAULT NULL, fingerprint VARCHAR(255) DEFAULT NULL, is_default TINYINT DEFAULT 0 NOT NULL, is_active TINYINT DEFAULT 1 NOT NULL, setup_status VARCHAR(30) NOT NULL, detached_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, billing_profile_id INT NOT NULL, UNIQUE INDEX UNIQ_8AFEFB3E2D13D9D5 (stripe_payment_method_id), INDEX IDX_8AFEFB3E409D7D29 (billing_profile_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE agency_subscription (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(30) NOT NULL, started_at DATETIME NOT NULL, current_period_start DATETIME DEFAULT NULL, current_period_end DATETIME DEFAULT NULL, cancel_at_period_end TINYINT DEFAULT 0 NOT NULL, canceled_at DATETIME DEFAULT NULL, ended_at DATETIME DEFAULT NULL, provider_customer_id VARCHAR(255) DEFAULT NULL, provider_subscription_id VARCHAR(255) DEFAULT NULL, provider_subscription_item_id VARCHAR(255) DEFAULT NULL, property_limit_snapshot INT DEFAULT NULL, included_boosts_snapshot INT DEFAULT 0 NOT NULL, boost_duration_days_snapshot INT DEFAULT 7 NOT NULL, amount_snapshot_minor BIGINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, agency_id INT NOT NULL, plan_id INT NOT NULL, plan_price_id INT DEFAULT NULL, currency_snapshot_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1F98F2F6424D71C9 (provider_subscription_id), INDEX IDX_1F98F2F6CDEADB2A (agency_id), INDEX IDX_1F98F2F6E899029B (plan_id), INDEX IDX_1F98F2F6D871F09D (plan_price_id), INDEX IDX_1F98F2F6CAD137FF (currency_snapshot_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE agency_subscription_period (id INT AUTO_INCREMENT NOT NULL, period_start DATETIME NOT NULL, period_end DATETIME NOT NULL, property_limit INT DEFAULT NULL, included_boosts INT DEFAULT 0 NOT NULL, amount_minor BIGINT DEFAULT 0 NOT NULL, status VARCHAR(30) NOT NULL, provider_invoice_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, subscription_id INT NOT NULL, currency_id INT NOT NULL, payment_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_8A7365A4C3FBD45 (provider_invoice_id), INDEX IDX_8A7365A49A1887DC (subscription_id), INDEX IDX_8A7365A438248176 (currency_id), INDEX IDX_8A7365A44C3A3BB (payment_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE billing_tax_identifier (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, country_code VARCHAR(2) NOT NULL, value VARCHAR(255) NOT NULL, stripe_tax_id VARCHAR(255) DEFAULT NULL, verification_status VARCHAR(30) NOT NULL, is_primary TINYINT DEFAULT 0 NOT NULL, verified_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, billing_profile_id INT NOT NULL, UNIQUE INDEX UNIQ_247C4B011AE559E4 (stripe_tax_id), INDEX IDX_247C4B01409D7D29 (billing_profile_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE booster_pack (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, boost_quantity INT NOT NULL, boost_duration_days INT DEFAULT 7 NOT NULL, is_active TINYINT DEFAULT 1 NOT NULL, position INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_2EF57F2C77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE booster_pack_price (id INT AUTO_INCREMENT NOT NULL, amount_minor BIGINT NOT NULL, payment_provider_price_id VARCHAR(255) DEFAULT NULL, is_active TINYINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, booster_pack_id INT NOT NULL, currency_id INT NOT NULL, UNIQUE INDEX UNIQ_C877F169B2C0EFFF (payment_provider_price_id), INDEX IDX_C877F16932661930 (booster_pack_id), INDEX IDX_C877F16938248176 (currency_id), UNIQUE INDEX uniq_booster_pack_currency (booster_pack_id, currency_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE booster_transaction (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, type VARCHAR(40) NOT NULL, expires_at DATETIME DEFAULT NULL, idempotency_key VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, agency_id INT NOT NULL, property_id INT DEFAULT NULL, booster_pack_id INT DEFAULT NULL, subscription_period_id INT DEFAULT NULL, payment_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_9EE4BCAE7FD1C147 (idempotency_key), INDEX IDX_9EE4BCAECDEADB2A (agency_id), INDEX IDX_9EE4BCAE549213EC (property_id), INDEX IDX_9EE4BCAE32661930 (booster_pack_id), INDEX IDX_9EE4BCAEDE34B862 (subscription_period_id), INDEX IDX_9EE4BCAE4C3A3BB (payment_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE credit_note (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(100) NOT NULL, status VARCHAR(20) NOT NULL, reason VARCHAR(255) NOT NULL, subtotal_minor BIGINT NOT NULL, tax_total_minor BIGINT NOT NULL, total_minor BIGINT NOT NULL, seller_snapshot JSON NOT NULL, customer_snapshot JSON NOT NULL, provider_credit_note_id VARCHAR(255) DEFAULT NULL, issued_at DATETIME DEFAULT NULL, voided_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, invoice_id INT NOT NULL, refund_id INT DEFAULT NULL, currency_id INT NOT NULL, UNIQUE INDEX UNIQ_C87F452996901F54 (number), UNIQUE INDEX UNIQ_C87F452944B4A0D2 (provider_credit_note_id), INDEX IDX_C87F45292989F1FD (invoice_id), UNIQUE INDEX UNIQ_C87F4529189801D5 (refund_id), INDEX IDX_C87F452938248176 (currency_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE credit_note_line (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, quantity NUMERIC(12, 3) NOT NULL, unit_amount_minor BIGINT NOT NULL, subtotal_minor BIGINT NOT NULL, tax_amount_minor BIGINT NOT NULL, total_minor BIGINT NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, credit_note_id INT NOT NULL, invoice_line_id INT DEFAULT NULL, INDEX IDX_B0A453BC1C696F7A (credit_note_id), INDEX IDX_B0A453BCBFA24391 (invoice_line_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(100) NOT NULL, status VARCHAR(30) NOT NULL, type VARCHAR(30) NOT NULL, subtotal_minor BIGINT NOT NULL, discount_total_minor BIGINT NOT NULL, taxable_total_minor BIGINT NOT NULL, tax_total_minor BIGINT NOT NULL, total_minor BIGINT NOT NULL, amount_paid_minor BIGINT NOT NULL, amount_due_minor BIGINT NOT NULL, amount_refunded_minor BIGINT NOT NULL, seller_snapshot JSON NOT NULL, customer_snapshot JSON NOT NULL, tax_snapshot JSON NOT NULL, provider_invoice_id VARCHAR(255) DEFAULT NULL, provider_invoice_pdf_url LONGTEXT DEFAULT NULL, provider_hosted_invoice_url LONGTEXT DEFAULT NULL, issued_at DATETIME DEFAULT NULL, due_at DATETIME DEFAULT NULL, paid_at DATETIME DEFAULT NULL, voided_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, agency_id INT NOT NULL, billing_profile_id INT NOT NULL, subscription_id INT DEFAULT NULL, subscription_period_id INT DEFAULT NULL, payment_id INT DEFAULT NULL, currency_id INT NOT NULL, UNIQUE INDEX UNIQ_9065174496901F54 (number), UNIQUE INDEX UNIQ_90651744C3FBD45 (provider_invoice_id), INDEX IDX_90651744CDEADB2A (agency_id), INDEX IDX_90651744409D7D29 (billing_profile_id), INDEX IDX_906517449A1887DC (subscription_id), INDEX IDX_90651744DE34B862 (subscription_period_id), UNIQUE INDEX UNIQ_906517444C3A3BB (payment_id), INDEX IDX_9065174438248176 (currency_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE invoice_discount (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(100) DEFAULT NULL, description VARCHAR(255) NOT NULL, type VARCHAR(30) NOT NULL, percentage NUMERIC(8, 4) DEFAULT NULL, amount_minor BIGINT NOT NULL, provider_coupon_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, invoice_id INT NOT NULL, invoice_line_id INT DEFAULT NULL, INDEX IDX_FEC8EE562989F1FD (invoice_id), INDEX IDX_FEC8EE56BFA24391 (invoice_line_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE invoice_line (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, description VARCHAR(255) NOT NULL, quantity NUMERIC(12, 3) NOT NULL, unit_amount_minor BIGINT NOT NULL, subtotal_minor BIGINT NOT NULL, discount_amount_minor BIGINT NOT NULL, taxable_amount_minor BIGINT NOT NULL, tax_amount_minor BIGINT NOT NULL, total_minor BIGINT NOT NULL, period_start DATETIME DEFAULT NULL, period_end DATETIME DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, invoice_id INT NOT NULL, INDEX IDX_D3D1D6932989F1FD (invoice_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE invoice_tax (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, country_code VARCHAR(2) NOT NULL, region_code VARCHAR(50) DEFAULT NULL, rate NUMERIC(8, 5) NOT NULL, taxable_amount_minor BIGINT NOT NULL, amount_minor BIGINT NOT NULL, inclusive TINYINT NOT NULL, tax_behavior VARCHAR(30) NOT NULL, provider_tax_rate_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, invoice_id INT NOT NULL, invoice_line_id INT DEFAULT NULL, INDEX IDX_2670D5B52989F1FD (invoice_id), INDEX IDX_2670D5B5BFA24391 (invoice_line_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, status VARCHAR(40) NOT NULL, amount_subtotal_minor BIGINT NOT NULL, discount_amount_minor BIGINT NOT NULL, tax_amount_minor BIGINT NOT NULL, amount_total_minor BIGINT NOT NULL, amount_paid_minor BIGINT NOT NULL, amount_refunded_minor BIGINT NOT NULL, exchange_rate NUMERIC(18, 8) DEFAULT NULL, gross_settlement_amount_minor BIGINT NOT NULL, fee_settlement_amount_minor BIGINT NOT NULL, net_settlement_amount_minor BIGINT NOT NULL, provider VARCHAR(30) NOT NULL, provider_payment_intent_id VARCHAR(255) DEFAULT NULL, provider_charge_id VARCHAR(255) DEFAULT NULL, provider_invoice_id VARCHAR(255) DEFAULT NULL, provider_checkout_session_id VARCHAR(255) DEFAULT NULL, provider_balance_transaction_id VARCHAR(255) DEFAULT NULL, payment_method_snapshot JSON NOT NULL, metadata JSON NOT NULL, failure_code VARCHAR(255) DEFAULT NULL, failure_message LONGTEXT DEFAULT NULL, authorized_at DATETIME DEFAULT NULL, paid_at DATETIME DEFAULT NULL, failed_at DATETIME DEFAULT NULL, canceled_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, agency_id INT NOT NULL, billing_profile_id INT NOT NULL, payment_method_id INT DEFAULT NULL, subscription_id INT DEFAULT NULL, booster_pack_id INT DEFAULT NULL, currency_id INT NOT NULL, settlement_currency_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_6D28840DAEA34913 (reference), UNIQUE INDEX UNIQ_6D28840DE8AB5F8C (provider_payment_intent_id), UNIQUE INDEX UNIQ_6D28840D8CB651A6 (provider_charge_id), UNIQUE INDEX UNIQ_6D28840D43276AFC (provider_checkout_session_id), INDEX IDX_6D28840DCDEADB2A (agency_id), INDEX IDX_6D28840D409D7D29 (billing_profile_id), INDEX IDX_6D28840D5AA1164F (payment_method_id), INDEX IDX_6D28840D9A1887DC (subscription_id), INDEX IDX_6D28840D32661930 (booster_pack_id), INDEX IDX_6D28840D38248176 (currency_id), INDEX IDX_6D28840D9978136E (settlement_currency_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE payment_attempt (id INT AUTO_INCREMENT NOT NULL, attempt_number INT NOT NULL, status VARCHAR(30) NOT NULL, provider_payment_intent_id VARCHAR(255) DEFAULT NULL, provider_charge_id VARCHAR(255) DEFAULT NULL, amount_minor BIGINT NOT NULL, requires_action_type VARCHAR(100) DEFAULT NULL, decline_code VARCHAR(100) DEFAULT NULL, failure_code VARCHAR(100) DEFAULT NULL, failure_message LONGTEXT DEFAULT NULL, completed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, payment_id INT NOT NULL, payment_method_id INT DEFAULT NULL, currency_id INT NOT NULL, INDEX IDX_1A50C8C4C3A3BB (payment_id), INDEX IDX_1A50C8C5AA1164F (payment_method_id), INDEX IDX_1A50C8C38248176 (currency_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE payment_fee (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(60) NOT NULL, amount_minor BIGINT NOT NULL, provider_balance_transaction_id VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, is_refundable TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, payment_id INT NOT NULL, refund_id INT DEFAULT NULL, currency_id INT NOT NULL, INDEX IDX_A12AA5814C3A3BB (payment_id), INDEX IDX_A12AA581189801D5 (refund_id), INDEX IDX_A12AA58138248176 (currency_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE payment_webhook_event (id INT AUTO_INCREMENT NOT NULL, provider VARCHAR(30) NOT NULL, provider_event_id VARCHAR(255) NOT NULL, event_type VARCHAR(150) NOT NULL, api_version VARCHAR(50) DEFAULT NULL, livemode TINYINT NOT NULL, payload JSON NOT NULL, status VARCHAR(30) NOT NULL, attempt_count INT NOT NULL, received_at DATETIME NOT NULL, processed_at DATETIME DEFAULT NULL, failed_at DATETIME DEFAULT NULL, error_message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_4C7AE61339B58662 (provider_event_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE property_boost (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, canceled_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, property_id INT NOT NULL, agency_id INT NOT NULL, booster_transaction_id INT NOT NULL, INDEX IDX_9077E665549213EC (property_id), INDEX IDX_9077E665CDEADB2A (agency_id), UNIQUE INDEX UNIQ_9077E6655AB07211 (booster_transaction_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE refund (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(100) NOT NULL, status VARCHAR(30) NOT NULL, reason VARCHAR(50) NOT NULL, amount_minor BIGINT NOT NULL, provider_refund_id VARCHAR(255) DEFAULT NULL, provider_balance_transaction_id VARCHAR(255) DEFAULT NULL, failure_reason VARCHAR(255) DEFAULT NULL, metadata JSON NOT NULL, requested_at DATETIME NOT NULL, processed_at DATETIME DEFAULT NULL, failed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, payment_id INT NOT NULL, invoice_id INT DEFAULT NULL, currency_id INT NOT NULL, UNIQUE INDEX UNIQ_5B2C1458AEA34913 (reference), UNIQUE INDEX UNIQ_5B2C1458C1061967 (provider_refund_id), INDEX IDX_5B2C14584C3A3BB (payment_id), INDEX IDX_5B2C14582989F1FD (invoice_id), INDEX IDX_5B2C145838248176 (currency_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE subscription_plan (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, property_limit INT DEFAULT NULL, included_boosts INT DEFAULT 0 NOT NULL, boost_duration_days INT DEFAULT 7 NOT NULL, is_free TINYINT DEFAULT 0 NOT NULL, is_default TINYINT DEFAULT 0 NOT NULL, is_active TINYINT DEFAULT 1 NOT NULL, position INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX uniq_subscription_plan_code (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE subscription_plan_price (id INT AUTO_INCREMENT NOT NULL, amount_minor BIGINT NOT NULL, payment_provider_price_id VARCHAR(255) DEFAULT NULL, is_active TINYINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, plan_id INT NOT NULL, currency_id INT NOT NULL, UNIQUE INDEX UNIQ_5B8B2740B2C0EFFF (payment_provider_price_id), INDEX IDX_5B8B2740E899029B (plan_id), INDEX IDX_5B8B274038248176 (currency_id), UNIQUE INDEX uniq_subscription_plan_currency (plan_id, currency_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE agency_billing_profile ADD CONSTRAINT FK_7F37F400CDEADB2A FOREIGN KEY (agency_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agency_billing_profile ADD CONSTRAINT FK_7F37F4007A5B1307 FOREIGN KEY (preferred_currency_id) REFERENCES devise (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE agency_billing_profile ADD CONSTRAINT FK_7F37F400AF212FD0 FOREIGN KEY (default_payment_method_id) REFERENCES agency_payment_method (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE agency_payment_method ADD CONSTRAINT FK_8AFEFB3E409D7D29 FOREIGN KEY (billing_profile_id) REFERENCES agency_billing_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agency_subscription ADD CONSTRAINT FK_1F98F2F6CDEADB2A FOREIGN KEY (agency_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agency_subscription ADD CONSTRAINT FK_1F98F2F6E899029B FOREIGN KEY (plan_id) REFERENCES subscription_plan (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE agency_subscription ADD CONSTRAINT FK_1F98F2F6D871F09D FOREIGN KEY (plan_price_id) REFERENCES subscription_plan_price (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE agency_subscription ADD CONSTRAINT FK_1F98F2F6CAD137FF FOREIGN KEY (currency_snapshot_id) REFERENCES devise (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE agency_subscription_period ADD CONSTRAINT FK_8A7365A49A1887DC FOREIGN KEY (subscription_id) REFERENCES agency_subscription (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agency_subscription_period ADD CONSTRAINT FK_8A7365A438248176 FOREIGN KEY (currency_id) REFERENCES devise (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE agency_subscription_period ADD CONSTRAINT FK_8A7365A44C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE billing_tax_identifier ADD CONSTRAINT FK_247C4B01409D7D29 FOREIGN KEY (billing_profile_id) REFERENCES agency_billing_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booster_pack_price ADD CONSTRAINT FK_C877F16932661930 FOREIGN KEY (booster_pack_id) REFERENCES booster_pack (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booster_pack_price ADD CONSTRAINT FK_C877F16938248176 FOREIGN KEY (currency_id) REFERENCES devise (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE booster_transaction ADD CONSTRAINT FK_9EE4BCAECDEADB2A FOREIGN KEY (agency_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booster_transaction ADD CONSTRAINT FK_9EE4BCAE549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE booster_transaction ADD CONSTRAINT FK_9EE4BCAE32661930 FOREIGN KEY (booster_pack_id) REFERENCES booster_pack (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE booster_transaction ADD CONSTRAINT FK_9EE4BCAEDE34B862 FOREIGN KEY (subscription_period_id) REFERENCES agency_subscription_period (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE booster_transaction ADD CONSTRAINT FK_9EE4BCAE4C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE credit_note ADD CONSTRAINT FK_C87F45292989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE credit_note ADD CONSTRAINT FK_C87F4529189801D5 FOREIGN KEY (refund_id) REFERENCES refund (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE credit_note ADD CONSTRAINT FK_C87F452938248176 FOREIGN KEY (currency_id) REFERENCES devise (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE credit_note_line ADD CONSTRAINT FK_B0A453BC1C696F7A FOREIGN KEY (credit_note_id) REFERENCES credit_note (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE credit_note_line ADD CONSTRAINT FK_B0A453BCBFA24391 FOREIGN KEY (invoice_line_id) REFERENCES invoice_line (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744CDEADB2A FOREIGN KEY (agency_id) REFERENCES `utilisateur` (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744409D7D29 FOREIGN KEY (billing_profile_id) REFERENCES agency_billing_profile (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517449A1887DC FOREIGN KEY (subscription_id) REFERENCES agency_subscription (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744DE34B862 FOREIGN KEY (subscription_period_id) REFERENCES agency_subscription_period (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517444C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174438248176 FOREIGN KEY (currency_id) REFERENCES devise (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE invoice_discount ADD CONSTRAINT FK_FEC8EE562989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice_discount ADD CONSTRAINT FK_FEC8EE56BFA24391 FOREIGN KEY (invoice_line_id) REFERENCES invoice_line (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice_line ADD CONSTRAINT FK_D3D1D6932989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice_tax ADD CONSTRAINT FK_2670D5B52989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice_tax ADD CONSTRAINT FK_2670D5B5BFA24391 FOREIGN KEY (invoice_line_id) REFERENCES invoice_line (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DCDEADB2A FOREIGN KEY (agency_id) REFERENCES `utilisateur` (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D409D7D29 FOREIGN KEY (billing_profile_id) REFERENCES agency_billing_profile (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D5AA1164F FOREIGN KEY (payment_method_id) REFERENCES agency_payment_method (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D9A1887DC FOREIGN KEY (subscription_id) REFERENCES agency_subscription (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D32661930 FOREIGN KEY (booster_pack_id) REFERENCES booster_pack (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D38248176 FOREIGN KEY (currency_id) REFERENCES devise (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D9978136E FOREIGN KEY (settlement_currency_id) REFERENCES devise (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE payment_attempt ADD CONSTRAINT FK_1A50C8C4C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payment_attempt ADD CONSTRAINT FK_1A50C8C5AA1164F FOREIGN KEY (payment_method_id) REFERENCES agency_payment_method (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE payment_attempt ADD CONSTRAINT FK_1A50C8C38248176 FOREIGN KEY (currency_id) REFERENCES devise (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE payment_fee ADD CONSTRAINT FK_A12AA5814C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payment_fee ADD CONSTRAINT FK_A12AA581189801D5 FOREIGN KEY (refund_id) REFERENCES refund (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE payment_fee ADD CONSTRAINT FK_A12AA58138248176 FOREIGN KEY (currency_id) REFERENCES devise (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE property_boost ADD CONSTRAINT FK_9077E665549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_boost ADD CONSTRAINT FK_9077E665CDEADB2A FOREIGN KEY (agency_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_boost ADD CONSTRAINT FK_9077E6655AB07211 FOREIGN KEY (booster_transaction_id) REFERENCES booster_transaction (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE refund ADD CONSTRAINT FK_5B2C14584C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE refund ADD CONSTRAINT FK_5B2C14582989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE refund ADD CONSTRAINT FK_5B2C145838248176 FOREIGN KEY (currency_id) REFERENCES devise (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE subscription_plan_price ADD CONSTRAINT FK_5B8B2740E899029B FOREIGN KEY (plan_id) REFERENCES subscription_plan (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subscription_plan_price ADD CONSTRAINT FK_5B8B274038248176 FOREIGN KEY (currency_id) REFERENCES devise (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agency_billing_profile DROP FOREIGN KEY FK_7F37F400CDEADB2A');
        $this->addSql('ALTER TABLE agency_billing_profile DROP FOREIGN KEY FK_7F37F4007A5B1307');
        $this->addSql('ALTER TABLE agency_billing_profile DROP FOREIGN KEY FK_7F37F400AF212FD0');
        $this->addSql('ALTER TABLE agency_payment_method DROP FOREIGN KEY FK_8AFEFB3E409D7D29');
        $this->addSql('ALTER TABLE agency_subscription DROP FOREIGN KEY FK_1F98F2F6CDEADB2A');
        $this->addSql('ALTER TABLE agency_subscription DROP FOREIGN KEY FK_1F98F2F6E899029B');
        $this->addSql('ALTER TABLE agency_subscription DROP FOREIGN KEY FK_1F98F2F6D871F09D');
        $this->addSql('ALTER TABLE agency_subscription DROP FOREIGN KEY FK_1F98F2F6CAD137FF');
        $this->addSql('ALTER TABLE agency_subscription_period DROP FOREIGN KEY FK_8A7365A49A1887DC');
        $this->addSql('ALTER TABLE agency_subscription_period DROP FOREIGN KEY FK_8A7365A438248176');
        $this->addSql('ALTER TABLE agency_subscription_period DROP FOREIGN KEY FK_8A7365A44C3A3BB');
        $this->addSql('ALTER TABLE billing_tax_identifier DROP FOREIGN KEY FK_247C4B01409D7D29');
        $this->addSql('ALTER TABLE booster_pack_price DROP FOREIGN KEY FK_C877F16932661930');
        $this->addSql('ALTER TABLE booster_pack_price DROP FOREIGN KEY FK_C877F16938248176');
        $this->addSql('ALTER TABLE booster_transaction DROP FOREIGN KEY FK_9EE4BCAECDEADB2A');
        $this->addSql('ALTER TABLE booster_transaction DROP FOREIGN KEY FK_9EE4BCAE549213EC');
        $this->addSql('ALTER TABLE booster_transaction DROP FOREIGN KEY FK_9EE4BCAE32661930');
        $this->addSql('ALTER TABLE booster_transaction DROP FOREIGN KEY FK_9EE4BCAEDE34B862');
        $this->addSql('ALTER TABLE booster_transaction DROP FOREIGN KEY FK_9EE4BCAE4C3A3BB');
        $this->addSql('ALTER TABLE credit_note DROP FOREIGN KEY FK_C87F45292989F1FD');
        $this->addSql('ALTER TABLE credit_note DROP FOREIGN KEY FK_C87F4529189801D5');
        $this->addSql('ALTER TABLE credit_note DROP FOREIGN KEY FK_C87F452938248176');
        $this->addSql('ALTER TABLE credit_note_line DROP FOREIGN KEY FK_B0A453BC1C696F7A');
        $this->addSql('ALTER TABLE credit_note_line DROP FOREIGN KEY FK_B0A453BCBFA24391');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744CDEADB2A');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744409D7D29');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517449A1887DC');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744DE34B862');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517444C3A3BB');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_9065174438248176');
        $this->addSql('ALTER TABLE invoice_discount DROP FOREIGN KEY FK_FEC8EE562989F1FD');
        $this->addSql('ALTER TABLE invoice_discount DROP FOREIGN KEY FK_FEC8EE56BFA24391');
        $this->addSql('ALTER TABLE invoice_line DROP FOREIGN KEY FK_D3D1D6932989F1FD');
        $this->addSql('ALTER TABLE invoice_tax DROP FOREIGN KEY FK_2670D5B52989F1FD');
        $this->addSql('ALTER TABLE invoice_tax DROP FOREIGN KEY FK_2670D5B5BFA24391');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DCDEADB2A');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D409D7D29');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D5AA1164F');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D9A1887DC');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D32661930');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D38248176');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D9978136E');
        $this->addSql('ALTER TABLE payment_attempt DROP FOREIGN KEY FK_1A50C8C4C3A3BB');
        $this->addSql('ALTER TABLE payment_attempt DROP FOREIGN KEY FK_1A50C8C5AA1164F');
        $this->addSql('ALTER TABLE payment_attempt DROP FOREIGN KEY FK_1A50C8C38248176');
        $this->addSql('ALTER TABLE payment_fee DROP FOREIGN KEY FK_A12AA5814C3A3BB');
        $this->addSql('ALTER TABLE payment_fee DROP FOREIGN KEY FK_A12AA581189801D5');
        $this->addSql('ALTER TABLE payment_fee DROP FOREIGN KEY FK_A12AA58138248176');
        $this->addSql('ALTER TABLE property_boost DROP FOREIGN KEY FK_9077E665549213EC');
        $this->addSql('ALTER TABLE property_boost DROP FOREIGN KEY FK_9077E665CDEADB2A');
        $this->addSql('ALTER TABLE property_boost DROP FOREIGN KEY FK_9077E6655AB07211');
        $this->addSql('ALTER TABLE refund DROP FOREIGN KEY FK_5B2C14584C3A3BB');
        $this->addSql('ALTER TABLE refund DROP FOREIGN KEY FK_5B2C14582989F1FD');
        $this->addSql('ALTER TABLE refund DROP FOREIGN KEY FK_5B2C145838248176');
        $this->addSql('ALTER TABLE subscription_plan_price DROP FOREIGN KEY FK_5B8B2740E899029B');
        $this->addSql('ALTER TABLE subscription_plan_price DROP FOREIGN KEY FK_5B8B274038248176');
        $this->addSql('DROP TABLE agency_billing_profile');
        $this->addSql('DROP TABLE agency_payment_method');
        $this->addSql('DROP TABLE agency_subscription');
        $this->addSql('DROP TABLE agency_subscription_period');
        $this->addSql('DROP TABLE billing_tax_identifier');
        $this->addSql('DROP TABLE booster_pack');
        $this->addSql('DROP TABLE booster_pack_price');
        $this->addSql('DROP TABLE booster_transaction');
        $this->addSql('DROP TABLE credit_note');
        $this->addSql('DROP TABLE credit_note_line');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_discount');
        $this->addSql('DROP TABLE invoice_line');
        $this->addSql('DROP TABLE invoice_tax');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payment_attempt');
        $this->addSql('DROP TABLE payment_fee');
        $this->addSql('DROP TABLE payment_webhook_event');
        $this->addSql('DROP TABLE property_boost');
        $this->addSql('DROP TABLE refund');
        $this->addSql('DROP TABLE subscription_plan');
        $this->addSql('DROP TABLE subscription_plan_price');
    }
}
