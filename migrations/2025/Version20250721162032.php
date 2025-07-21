<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250721162032 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE designation_poll_question ADD is_separator TINYINT(1) DEFAULT 0 NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE voting_platform_election_pool ADD is_separator TINYINT(1) DEFAULT 0 NOT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE designation_poll_question DROP is_separator
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE voting_platform_election_pool DROP is_separator
            SQL);
    }
}
