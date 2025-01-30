<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220524105827 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE election_vote_place (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(255) NOT NULL,
          alias VARCHAR(255) DEFAULT NULL,
          code VARCHAR(255) DEFAULT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          nb_addresses INT UNSIGNED DEFAULT 0 NOT NULL,
          nb_voters INT UNSIGNED DEFAULT 0 NOT NULL,
          delta_prediction_and_result_2017 DOUBLE PRECISION DEFAULT NULL,
          delta_average_predictions DOUBLE PRECISION DEFAULT NULL,
          abstentions_2017 DOUBLE PRECISION DEFAULT NULL,
          misregistrations_priority SMALLINT DEFAULT NULL,
          first_round_priority SMALLINT DEFAULT NULL,
          second_round_priority SMALLINT DEFAULT NULL,
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
          UNIQUE INDEX UNIQ_880DE20D77153098 (code),
          UNIQUE INDEX UNIQ_880DE20DD17F50A6 (uuid),
          INDEX IDX_880DE20D9F2C3FAB (zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          election_vote_place
        ADD
          CONSTRAINT FK_880DE20D9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE election_vote_place');
    }
}
