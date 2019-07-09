<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190705103704 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          contact_volunteer_team TINYINT(1) DEFAULT \'0\', 
        ADD 
          contact_running_mate_team TINYINT(1) DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        DROP 
          contact_volunteer_team, 
        DROP 
          contact_running_mate_team');
    }
}
