<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Packs boost : nom et description traduits (fr, en) dans booster_pack_translation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE booster_pack_translation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, locale VARCHAR(5) NOT NULL, translatable_id INT DEFAULT NULL, INDEX IDX_2663D9312C2AC5D3 (translatable_id), UNIQUE INDEX booster_pack_translation_unique_translation (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE booster_pack_translation ADD CONSTRAINT FK_2663D9312C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES booster_pack (id) ON DELETE CASCADE');

        // Reprise des données : le texte actuel devient la traduction française
        // et sert de base à l'anglaise, puis l'anglais des packs connus.
        $this->addSql("INSERT INTO booster_pack_translation (translatable_id, name, description, locale) SELECT id, name, description, 'fr' FROM booster_pack");
        $this->addSql("INSERT INTO booster_pack_translation (translatable_id, name, description, locale) SELECT id, name, description, 'en' FROM booster_pack");

        $english = [
            'boost-1' => ['1 boost', '1 boost applies to 1 listing and lasts 15 days.'],
            'boost-5' => ['5 boosts', '5 boosts to use on your listings for 15 days each.'],
            'boost-20' => ['20 boosts', '20 boosts to use on your listings for 15 days each.'],
        ];
        foreach ($english as $code => [$name, $description]) {
            $this->addSql(
                "UPDATE booster_pack_translation t INNER JOIN booster_pack p ON p.id = t.translatable_id SET t.name = ?, t.description = ? WHERE t.locale = 'en' AND p.code = ?",
                [$name, $description, $code],
            );
        }

        $this->addSql('ALTER TABLE booster_pack DROP name, DROP description');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE booster_pack ADD name VARCHAR(150) NOT NULL, ADD description LONGTEXT DEFAULT NULL');
        $this->addSql("UPDATE booster_pack p INNER JOIN booster_pack_translation t ON t.translatable_id = p.id AND t.locale = 'fr' SET p.name = t.name, p.description = t.description");
        $this->addSql('ALTER TABLE booster_pack_translation DROP FOREIGN KEY FK_2663D9312C2AC5D3');
        $this->addSql('DROP TABLE booster_pack_translation');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
