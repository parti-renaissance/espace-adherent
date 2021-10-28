<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201217161643 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_zone (
          base_event_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_BF208CAC3B1C4B73 (base_event_id),
          INDEX IDX_BF208CAC9F2C3FAB (zone_id),
          PRIMARY KEY(base_event_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          event_zone
        ADD
          CONSTRAINT FK_BF208CAC3B1C4B73 FOREIGN KEY (base_event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          event_zone
        ADD
          CONSTRAINT FK_BF208CAC9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE event_zone');
    }
}
