<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260613070710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_property_ville ON property (ville)');
        $this->addSql('CREATE INDEX idx_property_code_postal ON property (code_postal)');
        $this->addSql('CREATE INDEX idx_property_statut ON property (statut)');
        $this->addSql('CREATE INDEX idx_property_ville_statut ON property (ville, statut)');
        $this->addSql('CREATE INDEX idx_property_location ON property (latitude, longitude)');
        $this->addSql('ALTER TABLE property RENAME INDEX idx_8bf21cde95b4d7fa TO idx_property_type_bien');
        $this->addSql('ALTER TABLE property RENAME INDEX idx_8bf21cde7903e29b TO idx_property_type_transaction');
        $this->addSql('ALTER TABLE property RENAME INDEX idx_8bf21cdea76ed395 TO idx_property_user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_property_ville ON property');
        $this->addSql('DROP INDEX idx_property_code_postal ON property');
        $this->addSql('DROP INDEX idx_property_statut ON property');
        $this->addSql('DROP INDEX idx_property_ville_statut ON property');
        $this->addSql('DROP INDEX idx_property_location ON property');
        $this->addSql('ALTER TABLE property RENAME INDEX idx_property_type_transaction TO IDX_8BF21CDE7903E29B');
        $this->addSql('ALTER TABLE property RENAME INDEX idx_property_type_bien TO IDX_8BF21CDE95B4D7FA');
        $this->addSql('ALTER TABLE property RENAME INDEX idx_property_user TO IDX_8BF21CDEA76ED395');
    }
}
