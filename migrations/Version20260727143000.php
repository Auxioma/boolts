<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260727143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Stores agency profile visit totals as an integer counter.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE utilisateur MODIFY visit_agency INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE utilisateur MODIFY visit_agency VARCHAR(255) NOT NULL');
    }
}
