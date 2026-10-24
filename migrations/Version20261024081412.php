<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261024081412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Forfaits : nom et description traduits (fr, en) dans subscription_plan_translation';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE subscription_plan_translation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, locale VARCHAR(5) NOT NULL, translatable_id INT DEFAULT NULL, INDEX IDX_3390A8802C2AC5D3 (translatable_id), UNIQUE INDEX subscription_plan_translation_unique_translation (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE subscription_plan_translation ADD CONSTRAINT FK_3390A8802C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES subscription_plan (id) ON DELETE CASCADE');

        // Reprise des données : le texte actuel devient la traduction française
        // et sert de base à l'anglaise, puis l'anglais des forfaits connus.
        $this->addSql("INSERT INTO subscription_plan_translation (translatable_id, name, description, locale) SELECT id, name, description, 'fr' FROM subscription_plan");
        $this->addSql("INSERT INTO subscription_plan_translation (translatable_id, name, description, locale) SELECT id, name, description, 'en' FROM subscription_plan");

        $english = [
            'free-monthly' => ['Free', 'Publish your first listings for free.'],
            'debutant' => ['Beginner', 'For agencies starting their online activity.'],
            'confirme' => ['Experienced', 'For agencies with a large catalogue of listings.'],
            'free-annual' => ['Free', 'Publish your first listings for free (billed annually).'],
            'started' => ['Started', 'Annual plan to launch your online presence.'],
            'boss' => ['Boss', 'Annual plan for high-volume agencies.'],
        ];
        foreach ($english as $code => [$name, $description]) {
            $this->addSql(
                "UPDATE subscription_plan_translation t INNER JOIN subscription_plan p ON p.id = t.translatable_id SET t.name = ?, t.description = ? WHERE t.locale = 'en' AND p.code = ?",
                [$name, $description, $code],
            );
        }

        $this->addSql('ALTER TABLE subscription_plan DROP name, DROP description');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscription_plan ADD name VARCHAR(150) NOT NULL, ADD description LONGTEXT DEFAULT NULL');
        $this->addSql("UPDATE subscription_plan p INNER JOIN subscription_plan_translation t ON t.translatable_id = p.id AND t.locale = 'fr' SET p.name = t.name, p.description = t.description");
        $this->addSql('ALTER TABLE subscription_plan_translation DROP FOREIGN KEY FK_3390A8802C2AC5D3');
        $this->addSql('DROP TABLE subscription_plan_translation');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
