<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629182426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE caracteristique (id INT AUTO_INCREMENT NOT NULL, icone VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE caracteristique_translation (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, translatable_id INT DEFAULT NULL, INDEX IDX_9AB160AD2C2AC5D3 (translatable_id), UNIQUE INDEX caracteristique_translation_unique_translation (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE category_bien (id INT AUTO_INCREMENT NOT NULL, icone VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE category_bien_transaction (id INT AUTO_INCREMENT NOT NULL, icone VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE category_bien_transaction_translation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, translatable_id INT DEFAULT NULL, INDEX IDX_96E036FE2C2AC5D3 (translatable_id), UNIQUE INDEX category_bien_transaction_translation_unique_translation (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE category_bien_translation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, translatable_id INT DEFAULT NULL, INDEX IDX_8E410EED2C2AC5D3 (translatable_id), UNIQUE INDEX category_bien_translation_unique_translation (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, agence_id INT DEFAULT NULL, INDEX IDX_4C62E638D725330D (agence_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE devise (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, signe VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE favoris (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, property_id INT NOT NULL, INDEX IDX_8933C432A76ED395 (user_id), INDEX IDX_8933C432549213EC (property_id), UNIQUE INDEX UNIQ_FAVORIS_USER_PROPERTY (user_id, property_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE fuseau_horaire (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, utc VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE horaire_ouverture (id INT AUTO_INCREMENT NOT NULL, is_open TINYINT DEFAULT 0 NOT NULL, jour VARCHAR(20) DEFAULT NULL, ouverture_matin TIME DEFAULT NULL, fermeture_matin TIME DEFAULT NULL, ouverture_apres_midi TIME DEFAULT NULL, fermeture_apres_midi TIME DEFAULT NULL, agence_id INT NOT NULL, INDEX IDX_D97D2495D725330D (agence_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE langue_parler (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE langue_parler_user (langue_parler_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_8B8F5F4A89697D3E (langue_parler_id), INDEX IDX_8B8F5F4AA76ED395 (user_id), PRIMARY KEY (langue_parler_id, user_id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE langues (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, iso VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE pays (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, iso VARCHAR(5) NOT NULL, devise_id INT DEFAULT NULL, INDEX IDX_349F3CAEF4445056 (devise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE property (id INT AUTO_INCREMENT NOT NULL, code_postal VARCHAR(255) DEFAULT NULL, latitude NUMERIC(10, 7) DEFAULT NULL, longitude NUMERIC(10, 7) DEFAULT NULL, mapbox_id VARCHAR(255) DEFAULT NULL, feature_type VARCHAR(255) DEFAULT NULL, annee_construction VARCHAR(255) DEFAULT NULL, chambres VARCHAR(255) DEFAULT NULL, salle_de_bains VARCHAR(255) DEFAULT NULL, surface_total VARCHAR(255) DEFAULT NULL, dpe VARCHAR(255) DEFAULT NULL, dpe_lettre VARCHAR(255) DEFAULT NULL, ges VARCHAR(255) DEFAULT NULL, ges_lettre VARCHAR(255) DEFAULT NULL, dpe_min VARCHAR(255) DEFAULT NULL, dpe_max VARCHAR(255) DEFAULT NULL, date_indexation_energie DATETIME DEFAULT NULL, prix VARCHAR(255) DEFAULT NULL, reference_interne VARCHAR(255) DEFAULT NULL, montant_loyer_hors_charge VARCHAR(255) DEFAULT NULL, montant_depot_de_garantie VARCHAR(255) DEFAULT NULL, montant_des_charges VARCHAR(255) DEFAULT NULL, statut VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, session_id_mapbox VARCHAR(255) DEFAULT NULL, show_adresse TINYINT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, type_bien_id INT DEFAULT NULL, type_transaction_id INT DEFAULT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_8BF21CDE989D9B62 (slug), INDEX IDX_8BF21CDE95B4D7FA (type_bien_id), INDEX IDX_8BF21CDE7903E29B (type_transaction_id), INDEX IDX_8BF21CDEA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE property_caracteristique (property_id INT NOT NULL, caracteristique_id INT NOT NULL, INDEX IDX_D6F4BE49549213EC (property_id), INDEX IDX_D6F4BE491704EEB7 (caracteristique_id), PRIMARY KEY (property_id, caracteristique_id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE property_image (id INT AUTO_INCREMENT NOT NULL, image_name VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, position VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, property_id INT NOT NULL, INDEX IDX_32EC552549213EC (property_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE property_search_session (id BIGINT AUTO_INCREMENT NOT NULL, uuid BINARY(16) NOT NULL, transaction_type_id BIGINT NOT NULL, ville VARCHAR(180) DEFAULT NULL, cp VARCHAR(50) DEFAULT NULL, pays VARCHAR(180) NOT NULL, filters JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8B51E14AD17F50A6 (uuid), INDEX idx_property_search_session_uuid (uuid), INDEX idx_property_search_session_transaction_type (transaction_type_id), INDEX idx_property_search_session_ville (ville), INDEX idx_property_search_session_cp (cp), INDEX idx_property_search_session_pays (pays), INDEX idx_property_search_session_expires_at (expires_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE property_translation (id INT AUTO_INCREMENT NOT NULL, adresse VARCHAR(255) DEFAULT NULL, ville VARCHAR(255) DEFAULT NULL, pays VARCHAR(255) DEFAULT NULL, full_address VARCHAR(255) DEFAULT NULL, region VARCHAR(255) DEFAULT NULL, district VARCHAR(255) DEFAULT NULL, locality VARCHAR(255) DEFAULT NULL, neighborhood VARCHAR(255) DEFAULT NULL, poi VARCHAR(255) DEFAULT NULL, titre_du_logement VARCHAR(255) DEFAULT NULL, description_logement LONGTEXT DEFAULT NULL, locale VARCHAR(5) NOT NULL, translatable_id INT DEFAULT NULL, INDEX IDX_B0C85592C2AC5D3 (translatable_id), UNIQUE INDEX property_translation_unique_translation (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE property_view (id INT AUTO_INCREMENT NOT NULL, view_key VARCHAR(64) NOT NULL, visitor_hash VARCHAR(64) NOT NULL, viewed_at DATETIME NOT NULL, property_id INT NOT NULL, user_id INT DEFAULT NULL, INDEX idx_property_view_property (property_id), INDEX idx_property_view_user (user_id), UNIQUE INDEX uniq_property_view_key (view_key), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE translation (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, translation LONGTEXT NOT NULL, page VARCHAR(255) NOT NULL, UNIQUE INDEX uniq_translation_key_locale (`key`, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE user_translation (id INT AUTO_INCREMENT NOT NULL, adresse VARCHAR(255) DEFAULT NULL, adresse_complement VARCHAR(255) DEFAULT NULL, ville VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, adresse_contact VARCHAR(255) DEFAULT NULL, ville_contact VARCHAR(255) DEFAULT NULL, pays_contact VARCHAR(255) DEFAULT NULL, adresse_complement_contact VARCHAR(255) DEFAULT NULL, locale VARCHAR(5) NOT NULL, translatable_id INT DEFAULT NULL, INDEX IDX_1D728CFA2C2AC5D3 (translatable_id), UNIQUE INDEX user_translation_unique_translation (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE `utilisateur` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(250) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, is_verified TINYINT NOT NULL, email_auth_code VARCHAR(255) DEFAULT NULL, email_auth_code_expires_at DATETIME DEFAULT NULL, email_auth_code_requested_at DATETIME DEFAULT NULL, failed_verification_attempts INT DEFAULT 0 NOT NULL, email_auth_enabled TINYINT DEFAULT 0 NOT NULL, nom VARCHAR(255) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, code_postal VARCHAR(255) DEFAULT NULL, entreprise VARCHAR(255) DEFAULT NULL, email_contact VARCHAR(255) DEFAULT NULL, numero_contact VARCHAR(255) DEFAULT NULL, code_postal_contact VARCHAR(255) DEFAULT NULL, whats_app VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, last_login_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, pays_id INT DEFAULT NULL, langues_id INT DEFAULT NULL, devise_id INT DEFAULT NULL, fuseau_horaire_id INT DEFAULT NULL, INDEX IDX_1D1C63B3A6E44244 (pays_id), INDEX IDX_1D1C63B328EAE92 (langues_id), INDEX IDX_1D1C63B3F4445056 (devise_id), INDEX IDX_1D1C63B398DBDF9B (fuseau_horaire_id), INDEX IDX_USER_VERIFIED (is_verified), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE caracteristique_translation ADD CONSTRAINT FK_9AB160AD2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES caracteristique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_bien_transaction_translation ADD CONSTRAINT FK_96E036FE2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES category_bien_transaction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_bien_translation ADD CONSTRAINT FK_8E410EED2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES category_bien (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638D725330D FOREIGN KEY (agence_id) REFERENCES `utilisateur` (id)');
        $this->addSql('ALTER TABLE favoris ADD CONSTRAINT FK_8933C432A76ED395 FOREIGN KEY (user_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favoris ADD CONSTRAINT FK_8933C432549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE horaire_ouverture ADD CONSTRAINT FK_D97D2495D725330D FOREIGN KEY (agence_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE langue_parler_user ADD CONSTRAINT FK_8B8F5F4A89697D3E FOREIGN KEY (langue_parler_id) REFERENCES langue_parler (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE langue_parler_user ADD CONSTRAINT FK_8B8F5F4AA76ED395 FOREIGN KEY (user_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pays ADD CONSTRAINT FK_349F3CAEF4445056 FOREIGN KEY (devise_id) REFERENCES devise (id)');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE95B4D7FA FOREIGN KEY (type_bien_id) REFERENCES category_bien (id)');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE7903E29B FOREIGN KEY (type_transaction_id) REFERENCES category_bien_transaction (id)');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDEA76ED395 FOREIGN KEY (user_id) REFERENCES `utilisateur` (id)');
        $this->addSql('ALTER TABLE property_caracteristique ADD CONSTRAINT FK_D6F4BE49549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_caracteristique ADD CONSTRAINT FK_D6F4BE491704EEB7 FOREIGN KEY (caracteristique_id) REFERENCES caracteristique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_image ADD CONSTRAINT FK_32EC552549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_translation ADD CONSTRAINT FK_B0C85592C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES property (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_view ADD CONSTRAINT FK_E1E514B4549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_view ADD CONSTRAINT FK_E1E514B4A76ED395 FOREIGN KEY (user_id) REFERENCES `utilisateur` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES `utilisateur` (id)');
        $this->addSql('ALTER TABLE user_translation ADD CONSTRAINT FK_1D728CFA2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `utilisateur` ADD CONSTRAINT FK_1D1C63B3A6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id)');
        $this->addSql('ALTER TABLE `utilisateur` ADD CONSTRAINT FK_1D1C63B328EAE92 FOREIGN KEY (langues_id) REFERENCES langues (id)');
        $this->addSql('ALTER TABLE `utilisateur` ADD CONSTRAINT FK_1D1C63B3F4445056 FOREIGN KEY (devise_id) REFERENCES devise (id)');
        $this->addSql('ALTER TABLE `utilisateur` ADD CONSTRAINT FK_1D1C63B398DBDF9B FOREIGN KEY (fuseau_horaire_id) REFERENCES fuseau_horaire (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE caracteristique_translation DROP FOREIGN KEY FK_9AB160AD2C2AC5D3');
        $this->addSql('ALTER TABLE category_bien_transaction_translation DROP FOREIGN KEY FK_96E036FE2C2AC5D3');
        $this->addSql('ALTER TABLE category_bien_translation DROP FOREIGN KEY FK_8E410EED2C2AC5D3');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638D725330D');
        $this->addSql('ALTER TABLE favoris DROP FOREIGN KEY FK_8933C432A76ED395');
        $this->addSql('ALTER TABLE favoris DROP FOREIGN KEY FK_8933C432549213EC');
        $this->addSql('ALTER TABLE horaire_ouverture DROP FOREIGN KEY FK_D97D2495D725330D');
        $this->addSql('ALTER TABLE langue_parler_user DROP FOREIGN KEY FK_8B8F5F4A89697D3E');
        $this->addSql('ALTER TABLE langue_parler_user DROP FOREIGN KEY FK_8B8F5F4AA76ED395');
        $this->addSql('ALTER TABLE pays DROP FOREIGN KEY FK_349F3CAEF4445056');
        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDE95B4D7FA');
        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDE7903E29B');
        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDEA76ED395');
        $this->addSql('ALTER TABLE property_caracteristique DROP FOREIGN KEY FK_D6F4BE49549213EC');
        $this->addSql('ALTER TABLE property_caracteristique DROP FOREIGN KEY FK_D6F4BE491704EEB7');
        $this->addSql('ALTER TABLE property_image DROP FOREIGN KEY FK_32EC552549213EC');
        $this->addSql('ALTER TABLE property_translation DROP FOREIGN KEY FK_B0C85592C2AC5D3');
        $this->addSql('ALTER TABLE property_view DROP FOREIGN KEY FK_E1E514B4549213EC');
        $this->addSql('ALTER TABLE property_view DROP FOREIGN KEY FK_E1E514B4A76ED395');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE user_translation DROP FOREIGN KEY FK_1D728CFA2C2AC5D3');
        $this->addSql('ALTER TABLE `utilisateur` DROP FOREIGN KEY FK_1D1C63B3A6E44244');
        $this->addSql('ALTER TABLE `utilisateur` DROP FOREIGN KEY FK_1D1C63B328EAE92');
        $this->addSql('ALTER TABLE `utilisateur` DROP FOREIGN KEY FK_1D1C63B3F4445056');
        $this->addSql('ALTER TABLE `utilisateur` DROP FOREIGN KEY FK_1D1C63B398DBDF9B');
        $this->addSql('DROP TABLE caracteristique');
        $this->addSql('DROP TABLE caracteristique_translation');
        $this->addSql('DROP TABLE category_bien');
        $this->addSql('DROP TABLE category_bien_transaction');
        $this->addSql('DROP TABLE category_bien_transaction_translation');
        $this->addSql('DROP TABLE category_bien_translation');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE devise');
        $this->addSql('DROP TABLE favoris');
        $this->addSql('DROP TABLE fuseau_horaire');
        $this->addSql('DROP TABLE horaire_ouverture');
        $this->addSql('DROP TABLE langue_parler');
        $this->addSql('DROP TABLE langue_parler_user');
        $this->addSql('DROP TABLE langues');
        $this->addSql('DROP TABLE pays');
        $this->addSql('DROP TABLE property');
        $this->addSql('DROP TABLE property_caracteristique');
        $this->addSql('DROP TABLE property_image');
        $this->addSql('DROP TABLE property_search_session');
        $this->addSql('DROP TABLE property_translation');
        $this->addSql('DROP TABLE property_view');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE translation');
        $this->addSql('DROP TABLE user_translation');
        $this->addSql('DROP TABLE `utilisateur`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
