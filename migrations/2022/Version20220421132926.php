<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220421132926 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          my_team_delegated_access
        ADD
          created_at DATETIME DEFAULT NOW(),
        ADD
          updated_at DATETIME DEFAULT NOW()');

        $this->addSql('ALTER TABLE
          my_team_delegated_access
        CHANGE 
          created_at created_at DATETIME NOT NULL,
        CHANGE 
          updated_at updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE my_team_delegated_access DROP created_at, DROP updated_at');
    }
}
