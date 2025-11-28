<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221004155004 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_request (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          first_name VARCHAR(255) NOT NULL,
          last_name VARCHAR(255) NOT NULL,
          email VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          token CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          amount INT NOT NULL,
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
          UNIQUE INDEX UNIQ_BEE6BD11D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE adherent_request');
    }
}
