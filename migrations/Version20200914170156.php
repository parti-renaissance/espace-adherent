<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200914170156 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_election_pool DROP title');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          voting_platform_election_pool 
        ADD 
          title VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
