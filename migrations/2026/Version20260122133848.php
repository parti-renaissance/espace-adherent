<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260122133848 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE adherent_message_statistics (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  message_id INT UNSIGNED NOT NULL,
                  contacts INT UNSIGNED DEFAULT 0 NOT NULL,
                  visible_count INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_notifications INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_emails INT UNSIGNED DEFAULT NULL,
                  notifications_web INT UNSIGNED DEFAULT 0 NOT NULL,
                  notifications_ios INT UNSIGNED DEFAULT 0 NOT NULL,
                  notifications_android INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_impressions_list INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_impressions_timeline INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_impressions INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_opens_app INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_opens_app_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                  unique_opens_email INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_opens_email_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                  unique_opens_notification INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_opens_notification_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                  unique_opens_direct_link INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_opens_list INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_opens_timeline INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_opens INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_opens_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                  unique_clicks_app INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_clicks_app_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                  unique_clicks_email INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_clicks_email_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                  unique_clicks INT UNSIGNED DEFAULT 0 NOT NULL,
                  unique_clicks_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                  unsubscribed INT UNSIGNED DEFAULT 0 NOT NULL,
                  unsubscribed_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                  UNIQUE INDEX UNIQ_9891B709537A1329 (message_id),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_statistics
                ADD
                  CONSTRAINT FK_9891B709537A1329 FOREIGN KEY (message_id) REFERENCES adherent_messages (id) ON DELETE CASCADE
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_statistics DROP FOREIGN KEY FK_9891B709537A1329');
        $this->addSql('DROP TABLE adherent_message_statistics');
    }
}
