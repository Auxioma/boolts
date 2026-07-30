<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260727120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la date de mise en favoris pour les statistiques du tableau de bord.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_favoris_created_at ON favoris (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_favoris_created_at ON favoris');
    }
}
