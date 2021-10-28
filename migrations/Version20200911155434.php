<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200911155434 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE thematic_community_membership (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          community_id INT UNSIGNED DEFAULT NULL, 
          contact_id INT UNSIGNED DEFAULT NULL, 
          adherent_id INT UNSIGNED DEFAULT NULL, 
          elected_representative_id INT DEFAULT NULL, 
          joined_at DATETIME NOT NULL, 
          categories LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', 
          association TINYINT(1) DEFAULT \'0\' NOT NULL, 
          association_name VARCHAR(255) DEFAULT NULL, 
          motivation VARCHAR(255) NOT NULL, 
          expert TINYINT(1) DEFAULT \'0\' NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          type VARCHAR(255) NOT NULL, 
          INDEX IDX_22B6AC05FDA7B0BF (community_id), 
          INDEX IDX_22B6AC05E7A1254A (contact_id), 
          INDEX IDX_22B6AC0525F06C53 (adherent_id), 
          INDEX IDX_22B6AC05D38DA5D3 (elected_representative_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE thematic_community (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          description LONGTEXT NOT NULL, 
          enabled TINYINT(1) NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE thematic_community_contact (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          first_name VARCHAR(255) NOT NULL, 
          last_name VARCHAR(255) NOT NULL, 
          email VARCHAR(255) NOT NULL, 
          birth_date DATE NOT NULL, 
          phone VARCHAR(35) NOT NULL COMMENT \'(DC2Type:phone_number)\', 
          activity_area VARCHAR(255) NOT NULL, 
          job_area VARCHAR(255) NOT NULL, 
          job VARCHAR(255) NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          address_address VARCHAR(150) DEFAULT NULL, 
          address_postal_code VARCHAR(15) DEFAULT NULL, 
          address_city_insee VARCHAR(15) DEFAULT NULL, 
          address_city_name VARCHAR(255) DEFAULT NULL, 
          address_country VARCHAR(2) DEFAULT NULL, 
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', 
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', 
          address_region VARCHAR(255) DEFAULT NULL, 
          address_geocodable_hash VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          thematic_community_membership 
        ADD 
          CONSTRAINT FK_22B6AC05FDA7B0BF FOREIGN KEY (community_id) REFERENCES thematic_community (id)');
        $this->addSql('ALTER TABLE 
          thematic_community_membership 
        ADD 
          CONSTRAINT FK_22B6AC05E7A1254A FOREIGN KEY (contact_id) REFERENCES thematic_community_contact (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          thematic_community_membership 
        ADD 
          CONSTRAINT FK_22B6AC0525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          thematic_community_membership 
        ADD 
          CONSTRAINT FK_22B6AC05D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE thematic_community_membership DROP FOREIGN KEY FK_22B6AC05FDA7B0BF');
        $this->addSql('ALTER TABLE thematic_community_membership DROP FOREIGN KEY FK_22B6AC05E7A1254A');
        $this->addSql('DROP TABLE thematic_community_membership');
        $this->addSql('DROP TABLE thematic_community');
        $this->addSql('DROP TABLE thematic_community_contact');
    }
}
