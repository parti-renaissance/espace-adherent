<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704122345 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE ses_event (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  sns_message_id VARCHAR(255) NOT NULL,
                  event_type VARCHAR(50) DEFAULT NULL,
                  ses_message_id VARCHAR(255) DEFAULT NULL,
                  campaign_uuid VARCHAR(36) DEFAULT NULL,
                  adherent_uuid VARCHAR(36) DEFAULT NULL,
                  recipient VARCHAR(255) DEFAULT NULL,
                  occurred_at DATETIME DEFAULT NULL,
                  received_at DATETIME NOT NULL,
                  payload JSON NOT NULL,
                  UNIQUE INDEX UNIQ_6AF8C84FBBC2380E (sns_message_id),
                  INDEX IDX_6AF8C84FD0986B4493151B82 (campaign_uuid, event_type),
                  INDEX IDX_6AF8C84F5C0B87AD (ses_message_id),
                  PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_static_segment_member
                ADD
                  delivered_at DATETIME DEFAULT NULL,
                ADD
                  delayed_at DATETIME DEFAULT NULL,
                ADD
                  delay_type VARCHAR(255) DEFAULT NULL,
                ADD
                  rejected_at DATETIME DEFAULT NULL,
                ADD
                  reject_reason VARCHAR(255) DEFAULT NULL,
                ADD
                  bounced_at DATETIME DEFAULT NULL,
                ADD
                  bounce_sub_type VARCHAR(255) DEFAULT NULL,
                ADD
                  complained_at DATETIME DEFAULT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ses_event');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_static_segment_member
                DROP
                  delivered_at,
                DROP
                  delayed_at,
                DROP
                  delay_type,
                DROP
                  rejected_at,
                DROP
                  reject_reason,
                DROP
                  bounced_at,
                DROP
                  bounce_sub_type,
                DROP
                  complained_at
            SQL);
    }
}
