<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220223181739 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          first_round_priority SMALLINT DEFAULT NULL,
        ADD
          second_round_priority SMALLINT DEFAULT NULL,
        DROP
          first_round_misregistrations_priority,
        DROP
          second_round_misregistrations_priority');
        $this->addSql('ALTER TABLE
          pap_vote_place
        ADD
          first_round_priority SMALLINT DEFAULT NULL,
        ADD
          second_round_priority SMALLINT DEFAULT NULL,
        DROP
          first_round_misregistrations_priority,
        DROP
          second_round_misregistrations_priority');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          first_round_misregistrations_priority SMALLINT DEFAULT NULL,
        ADD
          second_round_misregistrations_priority SMALLINT DEFAULT NULL,
        DROP
          first_round_priority,
        DROP
          second_round_priority');
        $this->addSql('ALTER TABLE
          pap_vote_place
        ADD
          first_round_misregistrations_priority SMALLINT DEFAULT NULL,
        ADD
          second_round_misregistrations_priority SMALLINT DEFAULT NULL,
        DROP
          first_round_priority,
        DROP
          second_round_priority');
    }
}
