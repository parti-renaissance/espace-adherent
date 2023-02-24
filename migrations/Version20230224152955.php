<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230224152955 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE geo_vote_place (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          city_id INT UNSIGNED NOT NULL,
          district_id INT UNSIGNED NOT NULL,
          canton_id INT UNSIGNED DEFAULT NULL,
          geo_data_id INT UNSIGNED DEFAULT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          active TINYINT(1) NOT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_5C09B68877153098 (code),
          INDEX IDX_5C09B6888BAC62AF (city_id),
          INDEX IDX_5C09B688B08FA272 (district_id),
          INDEX IDX_5C09B6888D070D0B (canton_id),
          UNIQUE INDEX UNIQ_5C09B68880E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          geo_vote_place
        ADD
          CONSTRAINT FK_5C09B6888BAC62AF FOREIGN KEY (city_id) REFERENCES geo_city (id)');
        $this->addSql('ALTER TABLE
          geo_vote_place
        ADD
          CONSTRAINT FK_5C09B688B08FA272 FOREIGN KEY (district_id) REFERENCES geo_district (id)');
        $this->addSql('ALTER TABLE
          geo_vote_place
        ADD
          CONSTRAINT FK_5C09B6888D070D0B FOREIGN KEY (canton_id) REFERENCES geo_canton (id)');
        $this->addSql('ALTER TABLE
          geo_vote_place
        ADD
          CONSTRAINT FK_5C09B68880E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE geo_vote_place');
    }
}
