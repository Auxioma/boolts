<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261110120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Multi-devises des biens : code ISO sur devise, devise de référence sur property, table property_prix_devise (montants convertis)';
    }

    public function up(Schema $schema): void
    {
        // 1. Code ISO 4217 sur devise, repris du libellé existant "euro (EUR)".
        $this->addSql('ALTER TABLE devise ADD code VARCHAR(3) DEFAULT NULL');
        $this->addSql("UPDATE devise SET code = SUBSTRING(nom, CHAR_LENGTH(nom) - 3, 3) WHERE nom REGEXP '\\\\([A-Z]{3}\\\\)$'");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_43EDA4DF77153098 ON devise (code)');

        // 2. Devise de référence du bien = devise dans laquelle l'agence a saisi ses montants.
        $this->addSql('ALTER TABLE property ADD devise_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDEF4445056 FOREIGN KEY (devise_id) REFERENCES devise (id) ON DELETE RESTRICT');
        $this->addSql('CREATE INDEX IDX_8BF21CDEF4445056 ON property (devise_id)');
        // Biens existants : devise du profil agence (celle affichée lors de la saisie)…
        $this->addSql('UPDATE property p INNER JOIN `utilisateur` u ON u.id = p.user_id SET p.devise_id = u.devise_id WHERE p.devise_id IS NULL AND u.devise_id IS NOT NULL');
        // …sinon EUR, valeur affichée par défaut dans le formulaire.
        $this->addSql("UPDATE property SET devise_id = (SELECT id FROM devise WHERE code = 'EUR') WHERE devise_id IS NULL");

        // 3. Montants convertis par devise (les montants d'origine restent intacts dans property).
        $this->addSql('CREATE TABLE property_prix_devise (id INT AUTO_INCREMENT NOT NULL, prix NUMERIC(20, 2) DEFAULT NULL, montant_loyer_hors_charge NUMERIC(20, 2) DEFAULT NULL, montant_depot_de_garantie NUMERIC(20, 2) DEFAULT NULL, montant_des_charges NUMERIC(20, 2) DEFAULT NULL, taux_de_change NUMERIC(24, 10) NOT NULL, converted_at DATETIME NOT NULL, property_id INT NOT NULL, devise_id INT NOT NULL, INDEX IDX_70BB5774549213EC (property_id), INDEX IDX_70BB5774F4445056 (devise_id), UNIQUE INDEX uniq_property_prix_devise (property_id, devise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE property_prix_devise ADD CONSTRAINT FK_70BB5774549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_prix_devise ADD CONSTRAINT FK_70BB5774F4445056 FOREIGN KEY (devise_id) REFERENCES devise (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE property_prix_devise DROP FOREIGN KEY FK_70BB5774549213EC');
        $this->addSql('ALTER TABLE property_prix_devise DROP FOREIGN KEY FK_70BB5774F4445056');
        $this->addSql('DROP TABLE property_prix_devise');
        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDEF4445056');
        $this->addSql('DROP INDEX IDX_8BF21CDEF4445056 ON property');
        $this->addSql('ALTER TABLE property DROP devise_id');
        $this->addSql('DROP INDEX UNIQ_43EDA4DF77153098 ON devise');
        $this->addSql('ALTER TABLE devise DROP code');
    }
}
