<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200618163910 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          my_team_delegated_access CHANGE accesses accesses LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          my_team_delegated_access CHANGE accesses accesses LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:simple_array)\'');
    }
}
