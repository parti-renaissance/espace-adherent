<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250423143830 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_session_push_token_link (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          app_session_id INT UNSIGNED DEFAULT NULL,
          push_token_id INT UNSIGNED DEFAULT NULL,
          last_active_date DATETIME DEFAULT NULL,
          unsubscribed_at DATETIME DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_E592CD44D17F50A6 (uuid),
          INDEX IDX_E592CD44372447A3 (app_session_id),
          INDEX IDX_E592CD44258E0AE3 (push_token_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          app_session_push_token_link
        ADD
          CONSTRAINT FK_E592CD44372447A3 FOREIGN KEY (app_session_id) REFERENCES app_session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          app_session_push_token_link
        ADD
          CONSTRAINT FK_E592CD44258E0AE3 FOREIGN KEY (push_token_id) REFERENCES push_token (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE push_token DROP FOREIGN KEY FK_51BC1381372447A3');
        $this->addSql('DROP INDEX IDX_51BC1381372447A3 ON push_token');
        $this->addSql('ALTER TABLE
          push_token
        ADD
          unsubscribed_at DATETIME DEFAULT NULL,
        DROP
          app_session_id,
        DROP
          source');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_session_push_token_link DROP FOREIGN KEY FK_E592CD44372447A3');
        $this->addSql('ALTER TABLE app_session_push_token_link DROP FOREIGN KEY FK_E592CD44258E0AE3');
        $this->addSql('DROP TABLE app_session_push_token_link');
        $this->addSql('ALTER TABLE
          push_token
        ADD
          app_session_id INT UNSIGNED DEFAULT NULL,
        ADD
          source VARCHAR(255) DEFAULT NULL,
        DROP
          unsubscribed_at');
        $this->addSql('ALTER TABLE
          push_token
        ADD
          CONSTRAINT FK_51BC1381372447A3 FOREIGN KEY (app_session_id) REFERENCES app_session (id) ON
        UPDATE
          NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_51BC1381372447A3 ON push_token (app_session_id)');
    }
}
