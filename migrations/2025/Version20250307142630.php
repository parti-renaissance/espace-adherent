<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250307142630 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          children VARCHAR(255) DEFAULT NULL,
        ADD
          is_responsibility_waived TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event_inscription DROP children, DROP is_responsibility_waived');
    }
}
