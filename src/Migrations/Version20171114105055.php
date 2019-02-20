<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171114105055 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events_categories ADD status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL');
        $this->addSql('ALTER TABLE mooc_event_categories ADD status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL');
        $this->addSql('ALTER TABLE citizen_initiative_categories ADD status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_initiative_categories DROP status');
        $this->addSql('ALTER TABLE events_categories DROP status');
        $this->addSql('ALTER TABLE mooc_event_categories DROP status');
    }
}
