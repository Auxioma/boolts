<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261006123829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property_image CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY `FK_1D1C63B328EAE92`');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY `FK_1D1C63B3F4445056`');
        $this->addSql('ALTER TABLE utilisateur CHANGE langues_id langues_id INT DEFAULT NULL, CHANGE devise_id devise_id INT DEFAULT NULL, CHANGE fuseau_horaire_id fuseau_horaire_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B328EAE92 FOREIGN KEY (langues_id) REFERENCES langues (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3F4445056 FOREIGN KEY (devise_id) REFERENCES devise (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property_image CHANGE position position VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE `utilisateur` DROP FOREIGN KEY FK_1D1C63B328EAE92');
        $this->addSql('ALTER TABLE `utilisateur` DROP FOREIGN KEY FK_1D1C63B3F4445056');
        $this->addSql('ALTER TABLE `utilisateur` CHANGE langues_id langues_id INT NOT NULL, CHANGE devise_id devise_id INT NOT NULL, CHANGE fuseau_horaire_id fuseau_horaire_id INT NOT NULL');
        $this->addSql('ALTER TABLE `utilisateur` ADD CONSTRAINT `FK_1D1C63B328EAE92` FOREIGN KEY (langues_id) REFERENCES langues (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE `utilisateur` ADD CONSTRAINT `FK_1D1C63B3F4445056` FOREIGN KEY (devise_id) REFERENCES devise (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
