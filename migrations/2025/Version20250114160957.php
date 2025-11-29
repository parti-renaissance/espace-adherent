<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250114160957 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events_registrations CHANGE email_address email_address VARCHAR(255) DEFAULT NULL');

        $this->addSql('UPDATE events_registrations SET email_address = NULL WHERE email_address = \'\' OR email_address = \'null\'');
        $this->addSql('UPDATE events_registrations SET adherent_uuid = NULL WHERE adherent_uuid = \'\' OR adherent_uuid = \'null\'');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_EEFA30C06D80402471F7E88B ON events_registrations (adherent_uuid, event_id)');
        $this->addSql('ALTER TABLE
          events_registrations RENAME INDEX event_registration_email_address_idx TO IDX_EEFA30C0B08E074E');
        $this->addSql('ALTER TABLE
          events_registrations RENAME INDEX event_registration_adherent_uuid_idx TO IDX_EEFA30C06D804024');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_EEFA30C06D80402471F7E88B ON events_registrations');
        $this->addSql('ALTER TABLE events_registrations CHANGE email_address email_address VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE
          events_registrations RENAME INDEX idx_eefa30c06d804024 TO event_registration_adherent_uuid_idx');
        $this->addSql('ALTER TABLE
          events_registrations RENAME INDEX idx_eefa30c0b08e074e TO event_registration_email_address_idx');
    }
}
