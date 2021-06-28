<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210628130812 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE geo_vote_place (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          geo_data_id INT UNSIGNED DEFAULT NULL,
          city_name VARCHAR(255) DEFAULT NULL,
          insee_code VARCHAR(255) DEFAULT NULL,
          postal_code VARCHAR(255) DEFAULT NULL,
          address VARCHAR(255) DEFAULT NULL,
          closed_at DATE DEFAULT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          active TINYINT(1) NOT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          UNIQUE INDEX UNIQ_5C09B68877153098 (code),
          UNIQUE INDEX UNIQ_5C09B68880E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geo_vote_place_zone (
          vote_place_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_5D9E73DEF3F90B30 (vote_place_id),
          INDEX IDX_5D9E73DE9F2C3FAB (zone_id),
          PRIMARY KEY(vote_place_id, zone_id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          geo_vote_place
        ADD
          CONSTRAINT FK_5C09B68880E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          geo_vote_place_zone
        ADD
          CONSTRAINT FK_5D9E73DEF3F90B30 FOREIGN KEY (vote_place_id) REFERENCES geo_vote_place (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          geo_vote_place_zone
        ADD
          CONSTRAINT FK_5D9E73DE9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE geo_vote_place_zone DROP FOREIGN KEY FK_5D9E73DEF3F90B30');
        $this->addSql('DROP TABLE geo_vote_place');
        $this->addSql('DROP TABLE geo_vote_place_zone');
    }
}
