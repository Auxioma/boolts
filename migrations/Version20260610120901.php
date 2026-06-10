<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260610120901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE horaire_ouverture (id INT AUTO_INCREMENT NOT NULL, is_open TINYINT NOT NULL, ouverture_matin VARCHAR(255) NOT NULL, fermeture_matin VARCHAR(255) NOT NULL, ouverture_apres_midi VARCHAR(255) NOT NULL, fermeture_apres_midi VARCHAR(255) NOT NULL, agence_id INT DEFAULT NULL, INDEX IDX_D97D2495D725330D (agence_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE horaire_ouverture ADD CONSTRAINT FK_D97D2495D725330D FOREIGN KEY (agence_id) REFERENCES `utilisateur` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE horaire_ouverture DROP FOREIGN KEY FK_D97D2495D725330D');
        $this->addSql('DROP TABLE horaire_ouverture');
    }
}
