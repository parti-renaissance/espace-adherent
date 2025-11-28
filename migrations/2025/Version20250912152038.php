<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250912152038 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          ticket_uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
        ADD
          transport_detail LONGTEXT DEFAULT NULL,
        ADD
          accommodation_detail LONGTEXT DEFAULT NULL,
        ADD
          custom_detail LONGTEXT DEFAULT NULL');
        $this->addSql('UPDATE national_event_inscription SET ticket_uuid = UUID() WHERE ticket_uuid IS NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C3325557E3A4459E ON national_event_inscription (ticket_uuid)');
        $this->addSql('ALTER TABLE national_event_inscription MODIFY ticket_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_C3325557E3A4459E ON national_event_inscription');
        $this->addSql('ALTER TABLE
          national_event_inscription
        DROP
          ticket_uuid,
        DROP
          transport_detail,
        DROP
          accommodation_detail,
        DROP
          custom_detail');
    }
}
