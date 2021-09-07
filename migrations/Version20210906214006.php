<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210906214006 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          sms_campaign
        ADD
          recipient_count INT DEFAULT NULL,
        ADD
          response_payload LONGTEXT DEFAULT NULL,
        ADD
          external_id VARCHAR(255) DEFAULT NULL,
        ADD
          sent_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          sms_campaign
        DROP
          recipient_count,
        DROP
          response_payload,
        DROP
          external_id,
        DROP
          sent_at');
    }
}
