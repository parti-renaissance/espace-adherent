<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240205225155 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE openai_assistant (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          name VARCHAR(255) NOT NULL,
          open_ai_id VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_55C6718E5E237E06 (name),
          UNIQUE INDEX UNIQ_55C6718EF6E32D4D (open_ai_id),
          UNIQUE INDEX UNIQ_55C6718ED17F50A6 (uuid),
          INDEX IDX_55C6718E9DF5350C (created_by_administrator_id),
          INDEX IDX_55C6718ECF1918FF (updated_by_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE telegram_bot (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          name VARCHAR(255) NOT NULL,
          enabled TINYINT(1) DEFAULT 0 NOT NULL,
          api_token VARCHAR(255) NOT NULL,
          secret VARCHAR(255) NOT NULL,
          blacklisted_ids LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
          whitelisted_ids LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_CED6A3DF5E237E06 (name),
          UNIQUE INDEX UNIQ_CED6A3DF7BA2F5EB (api_token),
          UNIQUE INDEX UNIQ_CED6A3DF5CA2E8E5 (secret),
          UNIQUE INDEX UNIQ_CED6A3DFD17F50A6 (uuid),
          INDEX IDX_CED6A3DF9DF5350C (created_by_administrator_id),
          INDEX IDX_CED6A3DFCF1918FF (updated_by_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          openai_assistant
        ADD
          CONSTRAINT FK_55C6718E9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          openai_assistant
        ADD
          CONSTRAINT FK_55C6718ECF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          telegram_bot
        ADD
          CONSTRAINT FK_CED6A3DF9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          telegram_bot
        ADD
          CONSTRAINT FK_CED6A3DFCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('DROP INDEX UNIQ_7DC4B00477153098 ON chatbot');
        $this->addSql('ALTER TABLE
          chatbot
        ADD
          open_ai_assistant_id INT UNSIGNED DEFAULT NULL,
        ADD
          telegram_bot_id INT UNSIGNED DEFAULT NULL,
        ADD
          name VARCHAR(255) NOT NULL,
        ADD
          type VARCHAR(255) NOT NULL,
        ADD
          assistant_type VARCHAR(255) NOT NULL,
        DROP
          code,
        DROP
          assistant_id,
        DROP
          enabled,
        DROP
          telegram_bot_api_token,
        DROP
          telegram_bot_secret');
        $this->addSql('ALTER TABLE
          chatbot
        ADD
          CONSTRAINT FK_7DC4B0043FF15E50 FOREIGN KEY (open_ai_assistant_id) REFERENCES openai_assistant (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          chatbot
        ADD
          CONSTRAINT FK_7DC4B004A0E2F38 FOREIGN KEY (telegram_bot_id) REFERENCES telegram_bot (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7DC4B0045E237E06 ON chatbot (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7DC4B0043FF15E50 ON chatbot (open_ai_assistant_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7DC4B004A0E2F38 ON chatbot (telegram_bot_id)');
        $this->addSql('ALTER TABLE
          chatbot_message
        ADD
          assistant_id INT UNSIGNED DEFAULT NULL,
        ADD
          run_id INT UNSIGNED DEFAULT NULL,
        ADD
          entities JSON NOT NULL,
        CHANGE
          content text LONGTEXT NOT NULL,
        CHANGE
          external_id open_ai_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          chatbot_message
        ADD
          CONSTRAINT FK_EDF1E884E05387EF FOREIGN KEY (assistant_id) REFERENCES openai_assistant (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          chatbot_message
        ADD
          CONSTRAINT FK_EDF1E88484E3FEC4 FOREIGN KEY (run_id) REFERENCES chatbot_run (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EDF1E884F6E32D4D ON chatbot_message (open_ai_id)');
        $this->addSql('CREATE INDEX IDX_EDF1E884E05387EF ON chatbot_message (assistant_id)');
        $this->addSql('CREATE INDEX IDX_EDF1E88484E3FEC4 ON chatbot_message (run_id)');
        $this->addSql('ALTER TABLE
          chatbot_run
        ADD
          open_ai_id VARCHAR(255) DEFAULT NULL,
        CHANGE
          external_id open_ai_status VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D603CBB6F6E32D4D ON chatbot_run (open_ai_id)');
        $this->addSql('ALTER TABLE chatbot_thread CHANGE external_id open_ai_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A356AA3CF6E32D4D ON chatbot_thread (open_ai_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chatbot DROP FOREIGN KEY FK_7DC4B0043FF15E50');
        $this->addSql('ALTER TABLE chatbot_message DROP FOREIGN KEY FK_EDF1E884E05387EF');
        $this->addSql('ALTER TABLE chatbot DROP FOREIGN KEY FK_7DC4B004A0E2F38');
        $this->addSql('ALTER TABLE openai_assistant DROP FOREIGN KEY FK_55C6718E9DF5350C');
        $this->addSql('ALTER TABLE openai_assistant DROP FOREIGN KEY FK_55C6718ECF1918FF');
        $this->addSql('ALTER TABLE telegram_bot DROP FOREIGN KEY FK_CED6A3DF9DF5350C');
        $this->addSql('ALTER TABLE telegram_bot DROP FOREIGN KEY FK_CED6A3DFCF1918FF');
        $this->addSql('DROP TABLE openai_assistant');
        $this->addSql('DROP TABLE telegram_bot');
        $this->addSql('DROP INDEX UNIQ_7DC4B0045E237E06 ON chatbot');
        $this->addSql('DROP INDEX UNIQ_7DC4B0043FF15E50 ON chatbot');
        $this->addSql('DROP INDEX UNIQ_7DC4B004A0E2F38 ON chatbot');
        $this->addSql('ALTER TABLE
          chatbot
        ADD
          code VARCHAR(255) NOT NULL,
        ADD
          assistant_id VARCHAR(255) NOT NULL,
        ADD
          enabled TINYINT(1) DEFAULT 0 NOT NULL,
        ADD
          telegram_bot_api_token VARCHAR(255) DEFAULT NULL,
        ADD
          telegram_bot_secret VARCHAR(255) DEFAULT NULL,
        DROP
          open_ai_assistant_id,
        DROP
          telegram_bot_id,
        DROP
          name,
        DROP
          type,
        DROP
          assistant_type');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7DC4B00477153098 ON chatbot (code)');
        $this->addSql('ALTER TABLE chatbot_message DROP FOREIGN KEY FK_EDF1E88484E3FEC4');
        $this->addSql('DROP INDEX UNIQ_EDF1E884F6E32D4D ON chatbot_message');
        $this->addSql('DROP INDEX IDX_EDF1E884E05387EF ON chatbot_message');
        $this->addSql('DROP INDEX IDX_EDF1E88484E3FEC4 ON chatbot_message');
        $this->addSql('ALTER TABLE
          chatbot_message
        DROP
          assistant_id,
        DROP
          run_id,
        DROP
          entities,
        CHANGE
          text content LONGTEXT NOT NULL,
        CHANGE
          open_ai_id external_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_D603CBB6F6E32D4D ON chatbot_run');
        $this->addSql('ALTER TABLE
          chatbot_run
        ADD
          external_id VARCHAR(255) DEFAULT NULL,
        DROP
          open_ai_status,
        DROP
          open_ai_id');
        $this->addSql('DROP INDEX UNIQ_A356AA3CF6E32D4D ON chatbot_thread');
        $this->addSql('ALTER TABLE chatbot_thread CHANGE open_ai_id external_id VARCHAR(255) DEFAULT NULL');
    }
}
