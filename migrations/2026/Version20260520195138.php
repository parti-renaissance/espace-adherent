<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260520195138 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chatbot DROP FOREIGN KEY `FK_7DC4B0049DF5350C`');
        $this->addSql('ALTER TABLE chatbot DROP FOREIGN KEY `FK_7DC4B004CF1918FF`');
        $this->addSql('ALTER TABLE chatbot_run DROP FOREIGN KEY `FK_D603CBB6E2904019`');

        $this->addSql('ALTER TABLE chatbot_thread DROP FOREIGN KEY `FK_A356AA3C1984C580`');
        $this->addSql('ALTER TABLE chatbot_thread DROP FOREIGN KEY `FK_A356AA3C832A24AA`');
        $this->addSql('DROP INDEX IDX_A356AA3C1984C580 ON chatbot_thread');
        $this->addSql('DROP INDEX UNIQ_A356AA3C832A24AA ON chatbot_thread');

        $this->addSql('DROP TABLE chatbot');
        $this->addSql('DROP TABLE chatbot_run');
        $this->addSql('ALTER TABLE chatbot_message DROP external_id');

        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot_thread
                DROP
                  chatbot_id,
                DROP
                  current_run_id,
                DROP
                  external_id,
                DROP
                  telegram_chat_id
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE chatbot (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  created_by_administrator_id INT DEFAULT NULL,
                  updated_by_administrator_id INT DEFAULT NULL,
                  code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  assistant_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  enabled TINYINT DEFAULT 0 NOT NULL,
                  uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  telegram_bot_api_token VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
                  telegram_bot_secret VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
                  INDEX IDX_7DC4B0049DF5350C (created_by_administrator_id),
                  INDEX IDX_7DC4B004CF1918FF (updated_by_administrator_id),
                  UNIQUE INDEX UNIQ_7DC4B00477153098 (code),
                  UNIQUE INDEX UNIQ_7DC4B004D17F50A6 (uuid),
                  PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE chatbot_run (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  thread_id INT UNSIGNED NOT NULL,
                  status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  external_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
                  INDEX IDX_D603CBB6E2904019 (thread_id),
                  UNIQUE INDEX UNIQ_D603CBB6D17F50A6 (uuid),
                  PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot
                ADD
                  CONSTRAINT `FK_7DC4B0049DF5350C` FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON
                UPDATE
                  NO ACTION ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot
                ADD
                  CONSTRAINT `FK_7DC4B004CF1918FF` FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON
                UPDATE
                  NO ACTION ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot_run
                ADD
                  CONSTRAINT `FK_D603CBB6E2904019` FOREIGN KEY (thread_id) REFERENCES chatbot_thread (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql('ALTER TABLE chatbot_message ADD external_id VARCHAR(255) DEFAULT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot_thread
                ADD
                  chatbot_id INT UNSIGNED DEFAULT NULL,
                ADD
                  current_run_id INT UNSIGNED DEFAULT NULL,
                ADD
                  external_id VARCHAR(255) DEFAULT NULL,
                ADD
                  telegram_chat_id VARCHAR(255) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot_thread
                ADD
                  CONSTRAINT `FK_A356AA3C1984C580` FOREIGN KEY (chatbot_id) REFERENCES chatbot (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot_thread
                ADD
                  CONSTRAINT `FK_A356AA3C832A24AA` FOREIGN KEY (current_run_id) REFERENCES chatbot_run (id) ON
                UPDATE
                  NO ACTION ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('CREATE INDEX IDX_A356AA3C1984C580 ON chatbot_thread (chatbot_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A356AA3C832A24AA ON chatbot_thread (current_run_id)');
    }
}
