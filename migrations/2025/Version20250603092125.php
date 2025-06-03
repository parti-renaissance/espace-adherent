<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250603092125 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE events ADD agora_id INT UNSIGNED DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  events
                ADD
                  CONSTRAINT FK_5387574A57588F43 FOREIGN KEY (agora_id) REFERENCES agora (id) ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_5387574A57588F43 ON events (agora_id)
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE events_registrations ADD status VARCHAR(255) DEFAULT 'confirmed' NOT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE `events` DROP FOREIGN KEY FK_5387574A57588F43
            SQL);
        $this->addSql(<<<'SQL'
                DROP INDEX IDX_5387574A57588F43 ON `events`
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE `events` DROP agora_id
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE events_registrations DROP status
            SQL);
    }
}
