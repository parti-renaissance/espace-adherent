<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170601194424 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE invitations CHANGE email email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE events_registrations CHANGE email_address email_address VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE events_registrations CHANGE email_address email_address VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE invitations CHANGE email email VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci');
    }
}
