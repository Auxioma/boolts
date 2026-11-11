<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261111130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Messagerie : documents joints aux messages (PDF, Word, Excel…)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE messagerie_message ADD document_name VARCHAR(255) DEFAULT NULL, ADD document_original_name VARCHAR(255) DEFAULT NULL, ADD document_size INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE messagerie_message DROP document_name, DROP document_original_name, DROP document_size');
    }
}
