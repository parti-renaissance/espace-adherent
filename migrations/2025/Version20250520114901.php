<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250520114901 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE designation ADD enable_vote_questions_preview TINYINT(1) DEFAULT 1 NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE designation_poll_question ADD description LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE voting_platform_election_pool ADD description LONGTEXT DEFAULT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE designation DROP enable_vote_questions_preview
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE designation_poll_question DROP description
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE voting_platform_election_pool DROP description
            SQL);
    }
}
