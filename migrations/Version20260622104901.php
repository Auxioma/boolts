<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260622104901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE langue_parler (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE langue_parler_user (langue_parler_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_8B8F5F4A89697D3E (langue_parler_id), INDEX IDX_8B8F5F4AA76ED395 (user_id), PRIMARY KEY (langue_parler_id, user_id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE langue_parler_user ADD CONSTRAINT FK_8B8F5F4A89697D3E FOREIGN KEY (langue_parler_id) REFERENCES langue_parler (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE langue_parler_user ADD CONSTRAINT FK_8B8F5F4AA76ED395 FOREIGN KEY (user_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE langue_parler_user DROP FOREIGN KEY FK_8B8F5F4A89697D3E');
        $this->addSql('ALTER TABLE langue_parler_user DROP FOREIGN KEY FK_8B8F5F4AA76ED395');
        $this->addSql('DROP TABLE langue_parler');
        $this->addSql('DROP TABLE langue_parler_user');
    }
}
