<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180103105439 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE procuration_proxies_to_election_rounds (procuration_proxy_id INT NOT NULL, election_round_id INT NOT NULL, INDEX IDX_78DEA096E15E419B (procuration_proxy_id), INDEX IDX_78DEA096FCBF5E32 (election_round_id), PRIMARY KEY(procuration_proxy_id, election_round_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE procuration_proxies_to_election_rounds ADD CONSTRAINT FK_78DEA096E15E419B FOREIGN KEY (procuration_proxy_id) REFERENCES procuration_proxies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE procuration_proxies_to_election_rounds ADD CONSTRAINT FK_78DEA096FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE');
        $this->addSql('CREATE TABLE procuration_requests_to_election_rounds (procuration_request_id INT NOT NULL, election_round_id INT NOT NULL, INDEX IDX_A47BBD53128D9C53 (procuration_request_id), INDEX IDX_A47BBD53FCBF5E32 (election_round_id), PRIMARY KEY(procuration_request_id, election_round_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE procuration_requests_to_election_rounds ADD CONSTRAINT FK_A47BBD53128D9C53 FOREIGN KEY (procuration_request_id) REFERENCES procuration_requests (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE procuration_requests_to_election_rounds ADD CONSTRAINT FK_A47BBD53FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE procuration_proxies_to_election_rounds');
        $this->addSql('DROP TABLE procuration_requests_to_election_rounds');
    }
}
