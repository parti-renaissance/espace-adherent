<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220218121943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE procuration_vote_reminder (
          id INT AUTO_INCREMENT NOT NULL,
          procuration_request_id INT DEFAULT NULL,
          election_round_id INT DEFAULT NULL,
          processed_at DATETIME NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_DA7DAB5B128D9C53 (procuration_request_id),
          INDEX IDX_DA7DAB5BFCBF5E32 (election_round_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          procuration_vote_reminder
        ADD
          CONSTRAINT FK_DA7DAB5B128D9C53 FOREIGN KEY (procuration_request_id) REFERENCES procuration_requests (id)');
        $this->addSql('ALTER TABLE
          procuration_vote_reminder
        ADD
          CONSTRAINT FK_DA7DAB5BFCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE procuration_vote_reminder');
    }
}
