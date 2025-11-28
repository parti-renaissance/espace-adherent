<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250201142049 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_user_documents DROP FOREIGN KEY FK_7D14491F6A24B1A2');
        $this->addSql('ALTER TABLE event_user_documents DROP FOREIGN KEY FK_7D14491F71F7E88B');
        $this->addSql('DROP TABLE event_user_documents');
        $this->addSql('ALTER TABLE events DROP is_for_legislatives, DROP type, DROP renaissance_event');
        $this->addSql('ALTER TABLE event_zone DROP FOREIGN KEY FK_BF208CAC3B1C4B73');
        $this->addSql('DROP INDEX IDX_BF208CAC3B1C4B73 ON event_zone');
        $this->addSql('DROP INDEX `primary` ON event_zone');
        $this->addSql('ALTER TABLE event_zone CHANGE base_event_id event_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          event_zone
        ADD
          CONSTRAINT FK_BF208CAC71F7E88B FOREIGN KEY (event_id) REFERENCES `events` (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_BF208CAC71F7E88B ON event_zone (event_id)');
        $this->addSql('ALTER TABLE event_zone ADD PRIMARY KEY (event_id, zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_user_documents (
          event_id INT UNSIGNED NOT NULL,
          user_document_id INT UNSIGNED NOT NULL,
          INDEX IDX_7D14491F6A24B1A2 (user_document_id),
          INDEX IDX_7D14491F71F7E88B (event_id),
          PRIMARY KEY(event_id, user_document_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          event_user_documents
        ADD
          CONSTRAINT FK_7D14491F6A24B1A2 FOREIGN KEY (user_document_id) REFERENCES user_documents (id) ON
        UPDATE
          NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          event_user_documents
        ADD
          CONSTRAINT FK_7D14491F71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON
        UPDATE
          NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_zone DROP FOREIGN KEY FK_BF208CAC71F7E88B');
        $this->addSql('DROP INDEX IDX_BF208CAC71F7E88B ON event_zone');
        $this->addSql('DROP INDEX `PRIMARY` ON event_zone');
        $this->addSql('ALTER TABLE event_zone CHANGE event_id base_event_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          event_zone
        ADD
          CONSTRAINT FK_BF208CAC3B1C4B73 FOREIGN KEY (base_event_id) REFERENCES events (id) ON
        UPDATE
          NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_BF208CAC3B1C4B73 ON event_zone (base_event_id)');
        $this->addSql('ALTER TABLE event_zone ADD PRIMARY KEY (base_event_id, zone_id)');
        $this->addSql('ALTER TABLE
          `events`
        ADD
          is_for_legislatives TINYINT(1) DEFAULT 0,
        ADD
          type VARCHAR(255) NOT NULL,
        ADD
          renaissance_event TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
