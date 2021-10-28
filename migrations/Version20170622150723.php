<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170622150723 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE summaries ADD availabilities LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', ADD job_locations LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', DROP availability, DROP job_location, ADD `public` SMALLINT DEFAULT 0 NOT NULL, ADD showing_recent_activities SMALLINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE summaries ADD availability VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD job_location VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, DROP availabilities, DROP job_locations, DROP `public`, DROP showing_recent_activities');
    }
}
