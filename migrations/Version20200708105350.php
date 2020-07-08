<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200708105350 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          voting_platform_election 
        ADD 
          created_at DATETIME NOT NULL, 
        ADD 
          updated_at DATETIME NOT NULL');

        $this->addSql('UPDATE voting_platform_election SET created_at = NOW(), updated_at = NOW() WHERE created_at IS NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_election DROP created_at, DROP updated_at');
    }
}
