<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220124162602 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pap_campaign_vote_place (
          campaign_id INT UNSIGNED NOT NULL,
          vote_place_id INT UNSIGNED NOT NULL,
          INDEX IDX_9803BB72F639F774 (campaign_id),
          INDEX IDX_9803BB72F3F90B30 (vote_place_id),
          PRIMARY KEY(campaign_id, vote_place_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          pap_campaign_vote_place
        ADD
          CONSTRAINT FK_9803BB72F639F774 FOREIGN KEY (campaign_id) REFERENCES pap_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_campaign_vote_place
        ADD
          CONSTRAINT FK_9803BB72F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES pap_vote_place (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_vote_place
        ADD
          delta_prediction_and_result_2017 DOUBLE PRECISION DEFAULT NULL,
        ADD
          delta_average_predictions_2017 DOUBLE PRECISION DEFAULT NULL,
        ADD
          abstentions_2017 DOUBLE PRECISION DEFAULT NULL,
        ADD
          nb_misregistrations INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE pap_campaign_vote_place');
        $this->addSql('ALTER TABLE
          pap_vote_place
        DROP
          delta_prediction_and_result_2017,
        DROP
          delta_average_predictions_2017,
        DROP
          abstentions_2017,
        DROP
          nb_misregistrations');
    }
}
