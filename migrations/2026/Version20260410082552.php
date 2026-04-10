<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260410082552 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE notification_push_token (
                  notification_id INT UNSIGNED NOT NULL,
                  push_token_id INT UNSIGNED NOT NULL,
                  INDEX IDX_AACEB6A3EF1A9D84 (notification_id),
                  INDEX IDX_AACEB6A3258E0AE3 (push_token_id),
                  PRIMARY KEY(notification_id, push_token_id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  notification_push_token
                ADD
                  CONSTRAINT FK_AACEB6A3EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  notification_push_token
                ADD
                  CONSTRAINT FK_AACEB6A3258E0AE3 FOREIGN KEY (push_token_id) REFERENCES push_token (id) ON DELETE CASCADE
            SQL);
        $this->addSql('CREATE INDEX IDX_3D195599722ED869 ON app_session (unsubscribed_at)');
        $this->addSql('CREATE INDEX IDX_E592CD44722ED869 ON app_session_push_token_link (unsubscribed_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification_push_token DROP FOREIGN KEY FK_AACEB6A3EF1A9D84');
        $this->addSql('ALTER TABLE notification_push_token DROP FOREIGN KEY FK_AACEB6A3258E0AE3');
        $this->addSql('DROP TABLE notification_push_token');
        $this->addSql('DROP INDEX IDX_3D195599722ED869 ON app_session');
        $this->addSql('DROP INDEX IDX_E592CD44722ED869 ON app_session_push_token_link');
    }
}
