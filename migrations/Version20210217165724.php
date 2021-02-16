<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20210217165724 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE coalition_event_coalition (
          coalition_event_id INT UNSIGNED NOT NULL, 
          coalition_id INT UNSIGNED NOT NULL, 
          INDEX IDX_A9FDE72517D2912F (coalition_event_id), 
          INDEX IDX_A9FDE725C2A46A23 (coalition_id), 
          PRIMARY KEY(
            coalition_event_id, coalition_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          coalition_event_coalition 
        ADD 
          CONSTRAINT FK_A9FDE72517D2912F FOREIGN KEY (coalition_event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          coalition_event_coalition 
        ADD 
          CONSTRAINT FK_A9FDE725C2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AC2A46A23');
        $this->addSql('DROP INDEX IDX_5387574AC2A46A23 ON events');
        $this->addSql('ALTER TABLE events DROP coalition_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE coalition_event_coalition');
        $this->addSql('ALTER TABLE events ADD coalition_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          events 
        ADD 
          CONSTRAINT FK_5387574AC2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id)');
        $this->addSql('CREATE INDEX IDX_5387574AC2A46A23 ON events (coalition_id)');
    }
}
