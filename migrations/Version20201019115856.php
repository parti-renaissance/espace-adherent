<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201019115856 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          thematic_community_membership 
        ADD 
          motivations LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', 
        DROP 
          motivation');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          thematic_community_membership 
        ADD 
          motivation VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
        DROP 
          motivations');
    }
}
