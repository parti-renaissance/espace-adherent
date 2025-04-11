<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250411134834 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_request
        DROP
          token,
        DROP
          amount,
        DROP
          allow_email_notifications,
        DROP
          allow_mobile_notifications,
        DROP
          cleaned,
        DROP
          token_used_at,
        CHANGE
          adherent_uuid email_hash CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
        ADD
          account_created_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_request
        ADD
          token CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
        ADD
          amount DOUBLE PRECISION NOT NULL,
        ADD
          allow_email_notifications TINYINT(1) DEFAULT 0 NOT NULL,
        ADD
          allow_mobile_notifications TINYINT(1) DEFAULT 0 NOT NULL,
        ADD
          cleaned TINYINT(1) DEFAULT 0 NOT NULL,
        ADD
          token_used_at DATETIME DEFAULT NULL,
        CHANGE
          email_hash adherent_uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
        DROP
          account_created_at');
    }
}
