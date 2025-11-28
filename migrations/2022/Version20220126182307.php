<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220126182307 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
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
          misregistrations_priority_max INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_vote_place
        CHANGE
          nb_misregistrations misregistrations_priority SMALLINT DEFAULT NULL,
        CHANGE
          delta_average_predictions_2017 delta_average_predictions DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
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
          misregistrations_priority_max');
        $this->addSql('ALTER TABLE
          pap_vote_place
        ADD
          nb_misregistrations INT DEFAULT NULL,
        DROP
          misregistrations_priority,
        CHANGE
          delta_average_predictions delta_average_predictions_2017 DOUBLE PRECISION DEFAULT NULL');
    }
}
