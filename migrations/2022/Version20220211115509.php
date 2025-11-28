<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220211115509 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_proxies_to_election_rounds DROP FOREIGN KEY FK_D075F5A9E15E419B');
        $this->addSql('ALTER TABLE procuration_requests DROP FOREIGN KEY FK_9769FD842F1B6663');

        $this->addSql('ALTER TABLE
          procuration_proxies
        ADD
          voter_number VARCHAR(255) DEFAULT NULL,
        ADD
          other_vote_cities VARCHAR(255) DEFAULT NULL,
        ADD
          uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
        CHANGE
          gender gender VARCHAR(6) DEFAULT NULL,
        CHANGE
          last_name last_name VARCHAR(50) DEFAULT NULL,
        CHANGE
          first_names first_names VARCHAR(100) DEFAULT NULL,
        CHANGE
          id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9B5E777AD17F50A6 ON procuration_proxies (uuid)');
        $this->addSql('UPDATE procuration_proxies SET uuid = UUID()');
        $this->addSql('ALTER TABLE
          procuration_proxies
        CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE
          procuration_proxies_to_election_rounds
        CHANGE
          procuration_proxy_id procuration_proxy_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          procuration_requests
        CHANGE
          found_proxy_id found_proxy_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          procuration_proxies_to_election_rounds
        ADD
          CONSTRAINT FK_D075F5A9E15E419B FOREIGN KEY (procuration_proxy_id) REFERENCES procuration_proxies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_requests
        ADD
          CONSTRAINT FK_9769FD842F1B6663 FOREIGN KEY (found_proxy_id) REFERENCES procuration_proxies (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_9B5E777AD17F50A6 ON procuration_proxies');
        $this->addSql('ALTER TABLE
          procuration_proxies
        DROP
          voter_number,
        DROP
          other_vote_cities,
        DROP
          uuid,
        CHANGE
          id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE
          procuration_proxies_to_election_rounds
        CHANGE
          procuration_proxy_id procuration_proxy_id INT NOT NULL');
        $this->addSql('ALTER TABLE procuration_requests CHANGE found_proxy_id found_proxy_id INT DEFAULT NULL');
    }
}
