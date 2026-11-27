<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261128120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Home : vidéos de fond administrables (2 par langue)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE home_video (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, emplacement INT NOT NULL, video_name VARCHAR(255) DEFAULT NULL, poster_name VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_HOME_VIDEO_LOCALE_SLOT (locale, emplacement), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE home_video');
    }
}
