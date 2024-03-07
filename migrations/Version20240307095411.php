<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240307095411 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE procuration_v2_initial_requests (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          email VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          utm_source VARCHAR(255) DEFAULT NULL,
          utm_campaign VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_4BF11906D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE procuration_v2_proxies (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          vote_zone_id INT UNSIGNED NOT NULL,
          vote_place_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          round_id INT UNSIGNED NOT NULL,
          elector_number VARCHAR(9) NOT NULL,
          slots SMALLINT UNSIGNED DEFAULT 1 NOT NULL,
          email VARCHAR(255) NOT NULL,
          gender VARCHAR(6) NOT NULL,
          first_names VARCHAR(255) NOT NULL,
          last_name VARCHAR(100) NOT NULL,
          birthdate DATE NOT NULL,
          phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\',
          distant_vote_place TINYINT(1) DEFAULT 0 NOT NULL,
          client_ip VARCHAR(50) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          address_address VARCHAR(150) DEFAULT NULL,
          address_postal_code VARCHAR(15) DEFAULT NULL,
          address_city_insee VARCHAR(15) DEFAULT NULL,
          address_city_name VARCHAR(255) DEFAULT NULL,
          address_country VARCHAR(2) DEFAULT NULL,
          address_additional_address VARCHAR(150) DEFAULT NULL,
          address_region VARCHAR(255) DEFAULT NULL,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_geocodable_hash VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_4D04EBA4D17F50A6 (uuid),
          INDEX IDX_4D04EBA49DF5350C (created_by_administrator_id),
          INDEX IDX_4D04EBA4CF1918FF (updated_by_administrator_id),
          INDEX IDX_4D04EBA4149E6033 (vote_zone_id),
          INDEX IDX_4D04EBA4F3F90B30 (vote_place_id),
          INDEX IDX_4D04EBA425F06C53 (adherent_id),
          INDEX IDX_4D04EBA4A6005CA0 (round_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE procuration_v2_requests (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          vote_zone_id INT UNSIGNED NOT NULL,
          vote_place_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          round_id INT UNSIGNED NOT NULL,
          email VARCHAR(255) NOT NULL,
          gender VARCHAR(6) NOT NULL,
          first_names VARCHAR(255) NOT NULL,
          last_name VARCHAR(100) NOT NULL,
          birthdate DATE NOT NULL,
          phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\',
          distant_vote_place TINYINT(1) DEFAULT 0 NOT NULL,
          client_ip VARCHAR(50) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          address_address VARCHAR(150) DEFAULT NULL,
          address_postal_code VARCHAR(15) DEFAULT NULL,
          address_city_insee VARCHAR(15) DEFAULT NULL,
          address_city_name VARCHAR(255) DEFAULT NULL,
          address_country VARCHAR(2) DEFAULT NULL,
          address_additional_address VARCHAR(150) DEFAULT NULL,
          address_region VARCHAR(255) DEFAULT NULL,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_geocodable_hash VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_F6D458CBD17F50A6 (uuid),
          INDEX IDX_F6D458CB9DF5350C (created_by_administrator_id),
          INDEX IDX_F6D458CBCF1918FF (updated_by_administrator_id),
          INDEX IDX_F6D458CB149E6033 (vote_zone_id),
          INDEX IDX_F6D458CBF3F90B30 (vote_place_id),
          INDEX IDX_F6D458CB25F06C53 (adherent_id),
          INDEX IDX_F6D458CBA6005CA0 (round_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        ADD
          CONSTRAINT FK_4D04EBA49DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        ADD
          CONSTRAINT FK_4D04EBA4CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        ADD
          CONSTRAINT FK_4D04EBA4149E6033 FOREIGN KEY (vote_zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        ADD
          CONSTRAINT FK_4D04EBA4F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        ADD
          CONSTRAINT FK_4D04EBA425F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        ADD
          CONSTRAINT FK_4D04EBA4A6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CB9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CBCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CB149E6033 FOREIGN KEY (vote_zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CBF3F90B30 FOREIGN KEY (vote_place_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CB25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CBA6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP FOREIGN KEY FK_4D04EBA49DF5350C');
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP FOREIGN KEY FK_4D04EBA4CF1918FF');
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP FOREIGN KEY FK_4D04EBA4149E6033');
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP FOREIGN KEY FK_4D04EBA4F3F90B30');
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP FOREIGN KEY FK_4D04EBA425F06C53');
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP FOREIGN KEY FK_4D04EBA4A6005CA0');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CB9DF5350C');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CBCF1918FF');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CB149E6033');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CBF3F90B30');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CB25F06C53');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CBA6005CA0');
        $this->addSql('DROP TABLE procuration_v2_initial_requests');
        $this->addSql('DROP TABLE procuration_v2_proxies');
        $this->addSql('DROP TABLE procuration_v2_requests');
    }
}
