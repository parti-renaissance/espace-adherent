<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250826090334 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          validation_finished_at DATETIME DEFAULT NULL,
        CHANGE
          validation_expires_at validation_started_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          validation_expires_at DATETIME DEFAULT NULL,
        DROP
          validation_started_at,
        DROP
          validation_finished_at');
    }
}
