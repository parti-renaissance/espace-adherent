<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180604122006 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX event_registration_adherent_uuid_idx ON events_registrations (adherent_uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX event_registration_adherent_uuid_idx ON events_registrations');
    }
}
