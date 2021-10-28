<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210602113034 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events ADD coalition_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('UPDATE events AS ev
    INNER JOIN event_coalition ec on ev.id = ec.event_id
SET ev.coalition_id = ec.coalition_id');
        $this->addSql('DROP TABLE event_coalition');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574AC2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id)');
        $this->addSql('CREATE INDEX IDX_5387574AC2A46A23 ON events (coalition_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_coalition (
          event_id INT UNSIGNED NOT NULL,
          coalition_id INT UNSIGNED NOT NULL,
          UNIQUE INDEX UNIQ_215844FA71F7E88B (event_id),
          INDEX IDX_215844FAC2A46A23 (coalition_id),
          PRIMARY KEY(event_id, coalition_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          event_coalition
        ADD
          CONSTRAINT FK_215844FA71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          event_coalition
        ADD
          CONSTRAINT FK_215844FAC2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AC2A46A23');
        $this->addSql('DROP INDEX IDX_5387574AC2A46A23 ON events');
        $this->addSql('ALTER TABLE events DROP coalition_id');
    }
}
