<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Séquence de numérotation des factures Boolts.
 *
 * Un compteur mono-ligne incrémenté de +1 à chaque facture émise. Amorcé à
 * 100000 pour que la toute première facture porte le numéro « I-100001 ».
 */
final class Version20260901211804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table invoice_number_sequence (compteur des numéros de facture, départ I-100001).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE invoice_number_sequence (sequence_key VARCHAR(50) NOT NULL, `last_value` BIGINT NOT NULL, PRIMARY KEY (sequence_key)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql("INSERT INTO invoice_number_sequence (sequence_key, `last_value`) VALUES ('invoice', 100000)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE invoice_number_sequence');
    }
}
