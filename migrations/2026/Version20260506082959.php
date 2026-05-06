<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260506082959 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE adherent_message_targeted (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  message_id INT UNSIGNED NOT NULL,
                  adherent_id INT UNSIGNED DEFAULT NULL,
                  targeted_at DATETIME NOT NULL,
                  INDEX IDX_646FE8E325F06C53 (adherent_id),
                  INDEX IDX_646FE8E3537A1329 (message_id),
                  UNIQUE INDEX UNIQ_646FE8E325F06C53537A1329 (adherent_id, message_id),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE mailchimp_static_segment (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  campaign_id INT UNSIGNED NOT NULL,
                  mailchimp_segment_id INT DEFAULT NULL,
                  name VARCHAR(255) DEFAULT NULL,
                  filter_snapshot JSON DEFAULT NULL,
                  filter_hash VARCHAR(64) DEFAULT NULL,
                  built_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                  build_duration_ms INT UNSIGNED DEFAULT NULL,
                  expected_count INT UNSIGNED DEFAULT NULL,
                  prepared_count INT UNSIGNED DEFAULT NULL,
                  errored_count INT UNSIGNED DEFAULT NULL,
                  refused_count INT UNSIGNED DEFAULT NULL,
                  chunks_total INT UNSIGNED DEFAULT NULL,
                  chunks_done INT UNSIGNED DEFAULT 0 NOT NULL,
                  attempts INT UNSIGNED DEFAULT 0 NOT NULL,
                  error_summary LONGTEXT DEFAULT NULL,
                  UNIQUE INDEX UNIQ_ACFD323CF639F774 (campaign_id),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_targeted
                ADD
                  CONSTRAINT FK_646FE8E3537A1329 FOREIGN KEY (message_id) REFERENCES adherent_messages (id) ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_targeted
                ADD
                  CONSTRAINT FK_646FE8E325F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_static_segment
                ADD
                  CONSTRAINT FK_ACFD323CF639F774 FOREIGN KEY (campaign_id) REFERENCES mailchimp_campaign (id) ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_campaign
                ADD
                  preparation_status VARCHAR(255) DEFAULT 'not_started' NOT NULL,
                ADD
                  audience_check VARCHAR(255) DEFAULT NULL,
                ADD
                  block_reason VARCHAR(255) DEFAULT NULL,
                ADD
                  prepared_at DATETIME DEFAULT NULL,
                ADD
                  preparation_locked_by VARCHAR(255) DEFAULT NULL,
                ADD
                  preparation_failure_detail LONGTEXT DEFAULT NULL,
                ADD
                  cancellation_requested TINYINT(1) DEFAULT 0 NOT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_targeted DROP FOREIGN KEY FK_646FE8E3537A1329');
        $this->addSql('ALTER TABLE adherent_message_targeted DROP FOREIGN KEY FK_646FE8E325F06C53');
        $this->addSql('ALTER TABLE mailchimp_static_segment DROP FOREIGN KEY FK_ACFD323CF639F774');
        $this->addSql('DROP TABLE adherent_message_targeted');
        $this->addSql('DROP TABLE mailchimp_static_segment');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_campaign
                DROP
                  preparation_status,
                DROP
                  audience_check,
                DROP
                  block_reason,
                DROP
                  prepared_at,
                DROP
                  preparation_locked_by,
                DROP
                  preparation_failure_detail,
                DROP
                  cancellation_requested
            SQL);
    }
}
