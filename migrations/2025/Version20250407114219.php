<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250407114219 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_request
        DROP
          first_name,
        DROP
          last_name,
        DROP
          address_address,
        DROP
          address_postal_code,
        DROP
          address_city_insee,
        DROP
          address_city_name,
        DROP
          address_country,
        DROP
          address_region,
        DROP
          address_latitude,
        DROP
          address_longitude,
        DROP
          address_geocodable_hash,
        DROP
          password,
        DROP
          address_additional_address');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_request
        ADD
          first_name VARCHAR(255) NOT NULL,
        ADD
          last_name VARCHAR(255) NOT NULL,
        ADD
          address_address VARCHAR(150) DEFAULT NULL,
        ADD
          address_postal_code VARCHAR(15) DEFAULT NULL,
        ADD
          address_city_insee VARCHAR(15) DEFAULT NULL,
        ADD
          address_city_name VARCHAR(255) DEFAULT NULL,
        ADD
          address_country VARCHAR(2) DEFAULT NULL,
        ADD
          address_region VARCHAR(255) DEFAULT NULL,
        ADD
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          address_geocodable_hash VARCHAR(255) DEFAULT NULL,
        ADD
          password VARCHAR(255) DEFAULT NULL,
        ADD
          address_additional_address VARCHAR(255) DEFAULT NULL');
    }
}
