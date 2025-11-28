<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220608011640 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          election_vote_place
        DROP
          delta_prediction_and_result_2017,
        DROP
          delta_average_predictions,
        DROP
          abstentions_2017,
        DROP
          misregistrations_priority,
        DROP
          first_round_priority,
        DROP
          second_round_priority');
        $this->addSql('ALTER TABLE
          pap_campaign
        DROP
          delta_prediction_and_result_min_2017,
        DROP
          delta_prediction_and_result_max_2017,
        DROP
          delta_average_predictions_min,
        DROP
          delta_average_predictions_max,
        DROP
          abstentions_min_2017,
        DROP
          abstentions_max_2017,
        DROP
          misregistrations_priority_min,
        DROP
          misregistrations_priority_max,
        DROP
          first_round_priority,
        DROP
          second_round_priority');
        $this->addSql('ALTER TABLE
          pap_vote_place
        DROP
          delta_prediction_and_result_2017,
        DROP
          delta_average_predictions,
        DROP
          abstentions_2017,
        DROP
          misregistrations_priority,
        DROP
          first_round_priority,
        DROP
          second_round_priority');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          election_vote_place
        ADD
          delta_prediction_and_result_2017 DOUBLE PRECISION DEFAULT NULL,
        ADD
          delta_average_predictions DOUBLE PRECISION DEFAULT NULL,
        ADD
          abstentions_2017 DOUBLE PRECISION DEFAULT NULL,
        ADD
          misregistrations_priority SMALLINT DEFAULT NULL,
        ADD
          first_round_priority SMALLINT DEFAULT NULL,
        ADD
          second_round_priority SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          delta_prediction_and_result_min_2017 DOUBLE PRECISION DEFAULT NULL,
        ADD
          delta_prediction_and_result_max_2017 DOUBLE PRECISION DEFAULT NULL,
        ADD
          delta_average_predictions_min DOUBLE PRECISION DEFAULT NULL,
        ADD
          delta_average_predictions_max DOUBLE PRECISION DEFAULT NULL,
        ADD
          abstentions_min_2017 DOUBLE PRECISION DEFAULT NULL,
        ADD
          abstentions_max_2017 DOUBLE PRECISION DEFAULT NULL,
        ADD
          misregistrations_priority_min INT DEFAULT NULL,
        ADD
          misregistrations_priority_max INT DEFAULT NULL,
        ADD
          first_round_priority SMALLINT DEFAULT NULL,
        ADD
          second_round_priority SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_vote_place
        ADD
          delta_prediction_and_result_2017 DOUBLE PRECISION DEFAULT NULL,
        ADD
          delta_average_predictions DOUBLE PRECISION DEFAULT NULL,
        ADD
          abstentions_2017 DOUBLE PRECISION DEFAULT NULL,
        ADD
          misregistrations_priority SMALLINT DEFAULT NULL,
        ADD
          first_round_priority SMALLINT DEFAULT NULL,
        ADD
          second_round_priority SMALLINT DEFAULT NULL');
    }
}
