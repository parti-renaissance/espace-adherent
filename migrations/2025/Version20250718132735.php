<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250718132735 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription ADD public_id VARCHAR(7) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                CREATE UNIQUE INDEX UNIQ_C3325557B5B48B91 ON national_event_inscription (public_id)
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                DROP INDEX UNIQ_C3325557B5B48B91 ON national_event_inscription
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription DROP public_id
            SQL);
    }
}
