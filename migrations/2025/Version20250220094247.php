<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250220094247 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE referral (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          referrer_id INT UNSIGNED DEFAULT NULL,
          referred_id INT UNSIGNED DEFAULT NULL,
          email_address VARCHAR(255) NOT NULL,
          first_name VARCHAR(50) NOT NULL,
          last_name VARCHAR(50) DEFAULT NULL,
          nationality VARCHAR(2) DEFAULT NULL,
          phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\',
          birthdate DATE DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          address_address VARCHAR(255) DEFAULT NULL,
          address_additional_address VARCHAR(255) DEFAULT NULL,
          address_postal_code VARCHAR(255) DEFAULT NULL,
          address_city_insee VARCHAR(15) DEFAULT NULL,
          address_city_name VARCHAR(255) DEFAULT NULL,
          address_country VARCHAR(2) DEFAULT NULL,
          address_region VARCHAR(255) DEFAULT NULL,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_geocodable_hash VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_73079D00D17F50A6 (uuid),
          INDEX IDX_73079D00798C22DB (referrer_id),
          INDEX IDX_73079D00CFE2A98 (referred_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          referral
        ADD
          CONSTRAINT FK_73079D00798C22DB FOREIGN KEY (referrer_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          referral
        ADD
          CONSTRAINT FK_73079D00CFE2A98 FOREIGN KEY (referred_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referral DROP FOREIGN KEY FK_73079D00798C22DB');
        $this->addSql('ALTER TABLE referral DROP FOREIGN KEY FK_73079D00CFE2A98');
        $this->addSql('DROP TABLE referral');
    }
}
