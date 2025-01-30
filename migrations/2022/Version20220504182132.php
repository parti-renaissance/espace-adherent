<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220504182132 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          procuration_proxies_to_election_rounds
        ADD
          id INT UNSIGNED AUTO_INCREMENT NOT NULL FIRST,
        ADD
          french_request_available TINYINT(1) DEFAULT \'1\' NOT NULL,
        ADD
          foreign_request_available TINYINT(1) DEFAULT \'1\' NOT NULL,
        DROP
          PRIMARY KEY,
        ADD
          PRIMARY KEY (id)');
        $this->addSql('CREATE UNIQUE INDEX procuration_proxy_election_round_unique ON procuration_proxies_to_election_rounds (
          procuration_proxy_id, election_round_id
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_proxies_to_election_rounds MODIFY id INT UNSIGNED NOT NULL');
        $this->addSql('DROP INDEX procuration_proxy_election_round_unique ON procuration_proxies_to_election_rounds');
        $this->addSql('ALTER TABLE procuration_proxies_to_election_rounds DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE
          procuration_proxies_to_election_rounds
        DROP
          id,
        DROP
          french_request_available,
        DROP
          foreign_request_available');
        $this->addSql('ALTER TABLE
          procuration_proxies_to_election_rounds
        ADD
          PRIMARY KEY (
            procuration_proxy_id, election_round_id
          )');
    }
}
