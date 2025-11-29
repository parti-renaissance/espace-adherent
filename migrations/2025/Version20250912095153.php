<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250912095153 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE national_event_inscription_scan (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          inscription_id INT UNSIGNED DEFAULT NULL,
          scanned_by_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_284E9A8DD17F50A6 (uuid),
          INDEX IDX_284E9A8D5DAC5993 (inscription_id),
          INDEX IDX_284E9A8DEBBC642F (scanned_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          national_event_inscription_scan
        ADD
          CONSTRAINT FK_284E9A8D5DAC5993 FOREIGN KEY (inscription_id) REFERENCES national_event_inscription (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          national_event_inscription_scan
        ADD
          CONSTRAINT FK_284E9A8DEBBC642F FOREIGN KEY (scanned_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          last_ticket_scanned_at DATETIME DEFAULT NULL,
        CHANGE
          ticket_scanned_at first_ticket_scanned_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event_inscription_scan DROP FOREIGN KEY FK_284E9A8D5DAC5993');
        $this->addSql('ALTER TABLE national_event_inscription_scan DROP FOREIGN KEY FK_284E9A8DEBBC642F');
        $this->addSql('DROP TABLE national_event_inscription_scan');
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          ticket_scanned_at DATETIME DEFAULT NULL,
        DROP
          first_ticket_scanned_at,
        DROP
          last_ticket_scanned_at');
    }
}
