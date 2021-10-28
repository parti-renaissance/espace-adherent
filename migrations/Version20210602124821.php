<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210602124821 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events ADD cause_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('UPDATE events AS ev
    INNER JOIN event_cause ec on ev.id = ec.event_id
SET ev.cause_id = ec.cause_id');
        $this->addSql('DROP TABLE event_cause');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574A66E2221E FOREIGN KEY (cause_id) REFERENCES cause (id)');
        $this->addSql('CREATE INDEX IDX_5387574A66E2221E ON events (cause_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_cause (
          event_id INT UNSIGNED NOT NULL,
          cause_id INT UNSIGNED NOT NULL,
          UNIQUE INDEX UNIQ_B1C1CE9371F7E88B (event_id),
          INDEX IDX_B1C1CE9366E2221E (cause_id),
          PRIMARY KEY(event_id, cause_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          event_cause
        ADD
          CONSTRAINT FK_B1C1CE9366E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          event_cause
        ADD
          CONSTRAINT FK_B1C1CE9371F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A66E2221E');
        $this->addSql('DROP INDEX IDX_5387574A66E2221E ON events');
        $this->addSql('ALTER TABLE events DROP cause_id');
    }
}
