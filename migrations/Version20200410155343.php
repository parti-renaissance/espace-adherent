<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200410155343 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX ministry_vote_result_city_round_unique ON ministry_vote_result (city_id, election_round_id)');
        $this->addSql('CREATE UNIQUE INDEX city_vote_result_city_round_unique ON vote_result (city_id, election_round_id)');
        $this->addSql('CREATE UNIQUE INDEX vote_place_result_city_round_unique ON vote_result (
          vote_place_id, election_round_id
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX ministry_vote_result_city_round_unique ON ministry_vote_result');
        $this->addSql('DROP INDEX city_vote_result_city_round_unique ON vote_result');
        $this->addSql('DROP INDEX vote_place_result_city_round_unique ON vote_result');
    }
}
