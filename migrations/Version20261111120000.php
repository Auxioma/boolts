<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261111120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Messagerie : bien facultatif sur messagerie_conversation (contact direct particulier ↔ agence)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE messagerie_conversation CHANGE property_id property_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM messagerie_conversation WHERE property_id IS NULL');
        $this->addSql('ALTER TABLE messagerie_conversation CHANGE property_id property_id INT NOT NULL');
    }
}
