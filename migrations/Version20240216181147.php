<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240216181147 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE national_event (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          start_date DATETIME NOT NULL,
          end_date DATETIME NOT NULL,
          ticket_start_date DATETIME NOT NULL,
          ticket_end_date DATETIME NOT NULL,
          text_intro LONGTEXT DEFAULT NULL,
          text_help LONGTEXT DEFAULT NULL,
          text_confirmation LONGTEXT DEFAULT NULL,
          into_image_path VARCHAR(255) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          name VARCHAR(255) NOT NULL,
          canonical_name VARCHAR(255) NOT NULL,
          slug VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_AD037664D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE national_event_inscription (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          event_id INT UNSIGNED DEFAULT NULL,
          gender VARCHAR(6) NOT NULL,
          first_name VARCHAR(255) NOT NULL,
          last_name VARCHAR(255) NOT NULL,
          address_email VARCHAR(255) NOT NULL,
          postal_code VARCHAR(255) DEFAULT NULL,
          birthdate DATE DEFAULT NULL,
          phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\',
          join_newsletter TINYINT(1) DEFAULT 0 NOT NULL,
          client_ip VARCHAR(255) DEFAULT NULL,
          session_id VARCHAR(255) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          utm_source VARCHAR(255) DEFAULT NULL,
          utm_campaign VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_C3325557D17F50A6 (uuid),
          INDEX IDX_C332555771F7E88B (event_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          CONSTRAINT FK_C332555771F7E88B FOREIGN KEY (event_id) REFERENCES national_event (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event_inscription DROP FOREIGN KEY FK_C332555771F7E88B');
        $this->addSql('DROP TABLE national_event');
        $this->addSql('DROP TABLE national_event_inscription');
    }
}
