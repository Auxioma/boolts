<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260610140857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE horaire_ouverture DROP FOREIGN KEY `FK_D97D2495D725330D`');
        $this->addSql('ALTER TABLE horaire_ouverture CHANGE is_open is_open TINYINT DEFAULT 0 NOT NULL, CHANGE ouverture_matin ouverture_matin TIME DEFAULT NULL, CHANGE fermeture_matin fermeture_matin TIME DEFAULT NULL, CHANGE ouverture_apres_midi ouverture_apres_midi TIME DEFAULT NULL, CHANGE fermeture_apres_midi fermeture_apres_midi TIME DEFAULT NULL, CHANGE agence_id agence_id INT NOT NULL, CHANGE jour jour VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE horaire_ouverture ADD CONSTRAINT FK_D97D2495D725330D FOREIGN KEY (agence_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE horaire_ouverture DROP FOREIGN KEY FK_D97D2495D725330D');
        $this->addSql('ALTER TABLE horaire_ouverture CHANGE is_open is_open TINYINT NOT NULL, CHANGE jour jour VARCHAR(255) NOT NULL, CHANGE ouverture_matin ouverture_matin VARCHAR(255) NOT NULL, CHANGE fermeture_matin fermeture_matin VARCHAR(255) NOT NULL, CHANGE ouverture_apres_midi ouverture_apres_midi VARCHAR(255) NOT NULL, CHANGE fermeture_apres_midi fermeture_apres_midi VARCHAR(255) NOT NULL, CHANGE agence_id agence_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE horaire_ouverture ADD CONSTRAINT `FK_D97D2495D725330D` FOREIGN KEY (agence_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
