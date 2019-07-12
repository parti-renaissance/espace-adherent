<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190712133940 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          contact_only_volunteers TINYINT(1) DEFAULT \'0\', 
        ADD 
          contact_only_running_mates TINYINT(1) DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        DROP 
          contact_only_volunteers, 
        DROP 
          contact_only_running_mates');
    }
}
