<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220218134513 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE procuration_reminder (
          id INT AUTO_INCREMENT NOT NULL,
          procuration_request_id INT NOT NULL,
          election_round_id INT NOT NULL,
          processed_at DATETIME NOT NULL,
          type VARCHAR(255) NOT NULL,
          INDEX IDX_ACDB6495128D9C53 (procuration_request_id),
          INDEX IDX_ACDB6495FCBF5E32 (election_round_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          procuration_reminder
        ADD
          CONSTRAINT FK_ACDB6495128D9C53 FOREIGN KEY (procuration_request_id) REFERENCES procuration_requests (id)');
        $this->addSql('ALTER TABLE
          procuration_reminder
        ADD
          CONSTRAINT FK_ACDB6495FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE procuration_reminder');
    }
}
