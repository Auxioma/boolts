<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261107120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Messagerie instantanée : conversations (bien, agence, visiteur) et messages (texte, image, lu/non lu)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE messagerie_conversation (id INT AUTO_INCREMENT NOT NULL, last_message_at DATETIME DEFAULT NULL, last_message_preview VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, property_id INT NOT NULL, agence_id INT NOT NULL, visiteur_id INT NOT NULL, INDEX IDX_526CD53F549213EC (property_id), INDEX IDX_526CD53FD725330D (agence_id), INDEX IDX_526CD53F7F72333D (visiteur_id), INDEX IDX_CONVERSATION_AGENCE_LAST (agence_id, last_message_at), UNIQUE INDEX UNIQ_CONVERSATION_PROPERTY_VISITEUR (property_id, visiteur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messagerie_message (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, read_at DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, conversation_id INT NOT NULL, sender_id INT NOT NULL, INDEX IDX_FD8363039AC0396 (conversation_id), INDEX IDX_FD836303F624B39D (sender_id), INDEX IDX_MESSAGE_UNREAD (conversation_id, read_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE messagerie_conversation ADD CONSTRAINT FK_526CD53F549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messagerie_conversation ADD CONSTRAINT FK_526CD53FD725330D FOREIGN KEY (agence_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messagerie_conversation ADD CONSTRAINT FK_526CD53F7F72333D FOREIGN KEY (visiteur_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messagerie_message ADD CONSTRAINT FK_FD8363039AC0396 FOREIGN KEY (conversation_id) REFERENCES messagerie_conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messagerie_message ADD CONSTRAINT FK_FD836303F624B39D FOREIGN KEY (sender_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE messagerie_message DROP FOREIGN KEY FK_FD8363039AC0396');
        $this->addSql('ALTER TABLE messagerie_message DROP FOREIGN KEY FK_FD836303F624B39D');
        $this->addSql('ALTER TABLE messagerie_conversation DROP FOREIGN KEY FK_526CD53F549213EC');
        $this->addSql('ALTER TABLE messagerie_conversation DROP FOREIGN KEY FK_526CD53FD725330D');
        $this->addSql('ALTER TABLE messagerie_conversation DROP FOREIGN KEY FK_526CD53F7F72333D');
        $this->addSql('DROP TABLE messagerie_message');
        $this->addSql('DROP TABLE messagerie_conversation');
    }
}
