<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190712115245 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE application_request_volunteer ADD displayed TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE 
          application_request_running_mate 
        ADD 
          displayed TINYINT(1) DEFAULT \'1\' NOT NULL, 
          CHANGE is_local_association_member is_local_association_member TINYINT(1) DEFAULT \'0\' NOT NULL, 
          CHANGE is_political_activist is_political_activist TINYINT(1) DEFAULT \'0\' NOT NULL, 
          CHANGE is_previous_elected_official is_previous_elected_official TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          application_request_running_mate 
        DROP 
          displayed, 
          CHANGE is_local_association_member is_local_association_member TINYINT(1) NOT NULL, 
          CHANGE is_political_activist is_political_activist TINYINT(1) NOT NULL, 
          CHANGE is_previous_elected_official is_previous_elected_official TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE application_request_volunteer DROP displayed');
    }
}
