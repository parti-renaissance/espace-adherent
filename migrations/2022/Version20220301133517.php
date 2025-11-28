<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220301133517 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contact (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          first_name VARCHAR(50) NOT NULL,
          last_name VARCHAR(50) DEFAULT NULL,
          email_address VARCHAR(255) NOT NULL,
          phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\',
          birthdate DATE DEFAULT NULL,
          interests LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
          source VARCHAR(255) NOT NULL,
          mail_contact TINYINT(1) DEFAULT \'0\' NOT NULL,
          phone_contact TINYINT(1) DEFAULT \'0\' NOT NULL,
          cgu_accepted TINYINT(1) DEFAULT \'0\' NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          address_address VARCHAR(150) DEFAULT NULL,
          address_postal_code VARCHAR(15) DEFAULT NULL,
          address_city_insee VARCHAR(15) DEFAULT NULL,
          address_city_name VARCHAR(255) DEFAULT NULL,
          address_country VARCHAR(2) DEFAULT NULL,
          address_region VARCHAR(255) DEFAULT NULL,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_geocodable_hash VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_4C62E638B08E074E (email_address),
          UNIQUE INDEX UNIQ_4C62E638D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contact');
    }
}
