<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180531172925 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX event_registration_email_address_idx ON events_registrations (email_address)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX event_registration_email_address_idx ON events_registrations');
    }
}
