<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240221143404 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          events
        ADD
          visibility VARCHAR(255) DEFAULT \'public\' NOT NULL,
        ADD
          live_url VARCHAR(255) DEFAULT NULL');

        $this->addSql('UPDATE events SET visibility = \'private\' WHERE private = 1');

        $this->addSql('ALTER TABLE events DROP private');

        $this->addSql('ALTER TABLE event_group_category ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE events_categories ADD description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP visibility, DROP live_url, ADD private TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE event_group_category DROP description');
        $this->addSql('ALTER TABLE events_categories DROP description');
    }
}
