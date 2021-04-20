<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210420151005 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_coalition (
          event_id INT UNSIGNED NOT NULL,
          coalition_id INT UNSIGNED NOT NULL,
          UNIQUE INDEX UNIQ_215844FA71F7E88B (event_id),
          INDEX IDX_215844FAC2A46A23 (coalition_id),
          PRIMARY KEY(event_id, coalition_id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_cause (
          event_id INT UNSIGNED NOT NULL,
          cause_id INT UNSIGNED NOT NULL,
          UNIQUE INDEX UNIQ_B1C1CE9371F7E88B (event_id),
          INDEX IDX_B1C1CE9366E2221E (cause_id),
          PRIMARY KEY(event_id, cause_id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          event_coalition
        ADD
          CONSTRAINT FK_215844FA71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          event_coalition
        ADD
          CONSTRAINT FK_215844FAC2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          event_cause
        ADD
          CONSTRAINT FK_B1C1CE9371F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          event_cause
        ADD
          CONSTRAINT FK_B1C1CE9366E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE coalition_event_coalition');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE coalition_event_coalition (
          coalition_event_id INT UNSIGNED NOT NULL,
          coalition_id INT UNSIGNED NOT NULL,
          INDEX IDX_A9FDE725C2A46A23 (coalition_id),
          INDEX IDX_A9FDE72517D2912F (coalition_event_id),
          PRIMARY KEY(
            coalition_event_id, coalition_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          coalition_event_coalition
        ADD
          CONSTRAINT FK_A9FDE72517D2912F FOREIGN KEY (coalition_event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          coalition_event_coalition
        ADD
          CONSTRAINT FK_A9FDE725C2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE event_coalition');
        $this->addSql('DROP TABLE event_cause');
    }
}
