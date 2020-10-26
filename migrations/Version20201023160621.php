<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201023160621 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE referent_managed_area_zone (
          referent_managed_area_id INT NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_B3A7E3746B99CC25 (referent_managed_area_id),
          INDEX IDX_B3A7E3749F2C3FAB (zone_id),
          PRIMARY KEY(
            referent_managed_area_id, zone_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          referent_managed_area_zone
        ADD
          CONSTRAINT FK_B3A7E3746B99CC25 FOREIGN KEY (referent_managed_area_id) REFERENCES referent_managed_areas (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_managed_area_zone
        ADD
          CONSTRAINT FK_B3A7E3749F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE referent_managed_area_zone');
    }
}
