<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250120134757 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_EEFA30C06D80402471F7E88B ON events_registrations');
        $this->addSql('DROP INDEX IDX_EEFA30C06D804024 ON events_registrations');

        $this->addSql('ALTER TABLE events_registrations ADD adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('UPDATE events_registrations r SET adherent_id = (SELECT id FROM adherents a WHERE a.uuid = r.adherent_uuid)');
        $this->addSql('ALTER TABLE events_registrations DROP adherent_uuid');

        $this->addSql('ALTER TABLE
          events_registrations
        ADD
          CONSTRAINT FK_EEFA30C025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('CREATE INDEX IDX_EEFA30C025F06C53 ON events_registrations (adherent_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EEFA30C025F06C5371F7E88B ON events_registrations (adherent_id, event_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events_registrations DROP FOREIGN KEY FK_EEFA30C025F06C53');
        $this->addSql('DROP INDEX IDX_EEFA30C025F06C53 ON events_registrations');
        $this->addSql('DROP INDEX UNIQ_EEFA30C025F06C5371F7E88B ON events_registrations');
        $this->addSql('ALTER TABLE
          events_registrations
        ADD
          adherent_uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
        DROP
          adherent_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EEFA30C06D80402471F7E88B ON events_registrations (adherent_uuid, event_id)');
        $this->addSql('CREATE INDEX IDX_EEFA30C06D804024 ON events_registrations (adherent_uuid)');
    }
}
