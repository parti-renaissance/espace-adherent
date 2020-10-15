<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201015112931 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          thematic_community_membership 
        ADD 
          has_job TINYINT(1) DEFAULT \'0\' NOT NULL, 
        ADD 
          job VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE thematic_community_membership DROP has_job, DROP job');
    }
}
