<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260728084258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration vide : tables déjà créées dans la migration précédente.';
    }

    public function up(Schema $schema): void
    {
        // jai retirer les doublons
    }

    public function down(Schema $schema): void
    {
        // same ici !
    }
}