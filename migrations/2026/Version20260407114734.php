<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260407114734 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE push_notification (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  notification_class VARCHAR(255) NOT NULL,
                  title VARCHAR(255) NOT NULL,
                  body LONGTEXT NOT NULL,
                  scope VARCHAR(255) DEFAULT NULL,
                  data JSON DEFAULT NULL,
                  status VARCHAR(255) NOT NULL,
                  total_tokens INT UNSIGNED DEFAULT 0 NOT NULL,
                  total_success INT UNSIGNED DEFAULT 0 NOT NULL,
                  total_failed INT UNSIGNED DEFAULT 0 NOT NULL,
                  chunks_total INT UNSIGNED DEFAULT 0 NOT NULL,
                  chunks_delivered INT UNSIGNED DEFAULT 0 NOT NULL,
                  uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  UNIQUE INDEX UNIQ_4ABA22EAD17F50A6 (uuid),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  notification
                ADD
                  push_notification_id INT UNSIGNED DEFAULT NULL,
                ADD
                  tokens_sent SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
                ADD
                  tokens_success SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
                ADD
                  tokens_failed SMALLINT UNSIGNED DEFAULT 0 NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  notification
                ADD
                  CONSTRAINT FK_BF5476CA4E328CBE FOREIGN KEY (push_notification_id) REFERENCES push_notification (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('CREATE INDEX IDX_BF5476CA4E328CBE ON notification (push_notification_id)');
        $this->addSql('CREATE INDEX IDX_51BC1381722ED869 ON push_token (unsubscribed_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA4E328CBE');
        $this->addSql('DROP TABLE push_notification');
        $this->addSql('DROP INDEX IDX_BF5476CA4E328CBE ON notification');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  notification
                DROP
                  push_notification_id,
                DROP
                  tokens_sent,
                DROP
                  tokens_success,
                DROP
                  tokens_failed
            SQL);
        $this->addSql('DROP INDEX IDX_51BC1381722ED869 ON push_token');
    }
}
