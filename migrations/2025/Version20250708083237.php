<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250708083237 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  events
                ADD
                  adherents_up_to_date_count SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
                ADD
                  adherents_not_up_to_date_count SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
                ADD
                  sympathizers_count SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
                ADD
                  members_em_count SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
                ADD
                  citizens_count SMALLINT UNSIGNED DEFAULT 0 NOT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  `events`
                DROP
                  adherents_up_to_date_count,
                DROP
                  adherents_not_up_to_date_count,
                DROP
                  sympathizers_count,
                DROP
                  members_em_count,
                DROP
                  citizens_count
            SQL);
    }
}
