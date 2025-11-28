<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220221172957 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          first_round_misregistrations_priority SMALLINT DEFAULT NULL,
        ADD
          second_round_misregistrations_priority SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_vote_place
        ADD
          first_round_misregistrations_priority SMALLINT DEFAULT NULL,
        ADD
          second_round_misregistrations_priority SMALLINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          pap_campaign
        DROP
          first_round_misregistrations_priority,
        DROP
          second_round_misregistrations_priority');
        $this->addSql('ALTER TABLE
          pap_vote_place
        DROP
          first_round_misregistrations_priority,
        DROP
          second_round_misregistrations_priority');
    }
}
