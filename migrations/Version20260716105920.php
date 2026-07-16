<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260716105920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_property_view_viewed_at ON property_view (viewed_at)');
        $this->addSql('CREATE INDEX idx_property_view_property_date ON property_view (property_id, viewed_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_property_view_viewed_at ON property_view');
        $this->addSql('DROP INDEX idx_property_view_property_date ON property_view');
    }
}
