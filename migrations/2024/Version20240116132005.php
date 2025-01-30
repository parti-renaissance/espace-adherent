<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240116132005 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE chatbot (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          code VARCHAR(255) NOT NULL,
          assistant_id VARCHAR(255) NOT NULL,
          enabled TINYINT(1) DEFAULT 0 NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_7DC4B00477153098 (code),
          UNIQUE INDEX UNIQ_7DC4B004D17F50A6 (uuid),
          INDEX IDX_7DC4B0049DF5350C (created_by_administrator_id),
          INDEX IDX_7DC4B004CF1918FF (updated_by_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chatbot_message (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          thread_id INT UNSIGNED NOT NULL,
          role VARCHAR(255) NOT NULL,
          content LONGTEXT NOT NULL,
          date DATETIME NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          external_id VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_EDF1E884D17F50A6 (uuid),
          INDEX IDX_EDF1E884E2904019 (thread_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chatbot_run (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          thread_id INT UNSIGNED NOT NULL,
          status VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          external_id VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_D603CBB6D17F50A6 (uuid),
          INDEX IDX_D603CBB6E2904019 (thread_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chatbot_thread (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          chatbot_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          current_run_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          external_id VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_A356AA3CD17F50A6 (uuid),
          INDEX IDX_A356AA3C1984C580 (chatbot_id),
          INDEX IDX_A356AA3C25F06C53 (adherent_id),
          UNIQUE INDEX UNIQ_A356AA3C832A24AA (current_run_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          chatbot
        ADD
          CONSTRAINT FK_7DC4B0049DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          chatbot
        ADD
          CONSTRAINT FK_7DC4B004CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          chatbot_message
        ADD
          CONSTRAINT FK_EDF1E884E2904019 FOREIGN KEY (thread_id) REFERENCES chatbot_thread (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          chatbot_run
        ADD
          CONSTRAINT FK_D603CBB6E2904019 FOREIGN KEY (thread_id) REFERENCES chatbot_thread (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          chatbot_thread
        ADD
          CONSTRAINT FK_A356AA3C1984C580 FOREIGN KEY (chatbot_id) REFERENCES chatbot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          chatbot_thread
        ADD
          CONSTRAINT FK_A356AA3C25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          chatbot_thread
        ADD
          CONSTRAINT FK_A356AA3C832A24AA FOREIGN KEY (current_run_id) REFERENCES chatbot_run (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chatbot DROP FOREIGN KEY FK_7DC4B0049DF5350C');
        $this->addSql('ALTER TABLE chatbot DROP FOREIGN KEY FK_7DC4B004CF1918FF');
        $this->addSql('ALTER TABLE chatbot_message DROP FOREIGN KEY FK_EDF1E884E2904019');
        $this->addSql('ALTER TABLE chatbot_run DROP FOREIGN KEY FK_D603CBB6E2904019');
        $this->addSql('ALTER TABLE chatbot_thread DROP FOREIGN KEY FK_A356AA3C1984C580');
        $this->addSql('ALTER TABLE chatbot_thread DROP FOREIGN KEY FK_A356AA3C25F06C53');
        $this->addSql('ALTER TABLE chatbot_thread DROP FOREIGN KEY FK_A356AA3C832A24AA');
        $this->addSql('DROP TABLE chatbot');
        $this->addSql('DROP TABLE chatbot_message');
        $this->addSql('DROP TABLE chatbot_run');
        $this->addSql('DROP TABLE chatbot_thread');
    }
}
