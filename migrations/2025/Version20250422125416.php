<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250422125416 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_request_reminder (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_request_id INT UNSIGNED DEFAULT NULL,
          type VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_D80F7E4CD17F50A6 (uuid),
          INDEX IDX_D80F7E4C63A79B71 (adherent_request_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          adherent_request_reminder
        ADD
          CONSTRAINT FK_D80F7E4C63A79B71 FOREIGN KEY (adherent_request_id) REFERENCES adherent_request (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_request_reminder DROP FOREIGN KEY FK_D80F7E4C63A79B71');
        $this->addSql('DROP TABLE adherent_request_reminder');
    }
}
