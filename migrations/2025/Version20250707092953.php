<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250707092953 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  events
                ADD
                  members_count SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
                ADD
                  adherents_count SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
                ADD
                  sympathizers_count SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
                ADD
                  members_em_count SMALLINT UNSIGNED DEFAULT 0 NOT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  `events`
                DROP
                  members_count,
                DROP
                  adherents_count,
                DROP
                  sympathizers_count,
                DROP
                  members_em_count
            SQL);
    }
}
