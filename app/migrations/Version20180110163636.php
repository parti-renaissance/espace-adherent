<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180110163636 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE events_registrations ADD last_name VARCHAR(50) NOT NULL');
        $this->addSql('UPDATE events_registrations, adherents SET events_registrations.last_name = adherents.last_name WHERE events_registrations.adherent_uuid = adherents.uuid');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE events_registrations DROP last_name');
    }
}
