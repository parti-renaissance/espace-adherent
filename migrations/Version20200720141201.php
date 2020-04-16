<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200720141201 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          linkedin_page_url VARCHAR(255) DEFAULT NULL, 
        ADD 
          telegram_page_url VARCHAR(255) DEFAULT NULL,
        ADD 
          job VARCHAR(255) DEFAULT NULL, 
        ADD 
          activity_area VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherents 
        DROP 
          linkedin_page_url, 
        DROP 
          telegram_page_url,
        DROP 
          job, 
        DROP 
          activity_area');
    }
}
