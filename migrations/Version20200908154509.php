<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200908154509 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_election_pool ADD code VARCHAR(255) DEFAULT NULL');

        $this->addSql("UPDATE voting_platform_election_pool SET code = 'female' WHERE title = 'Femme'");
        $this->addSql("UPDATE voting_platform_election_pool SET code = 'male' WHERE title = 'Homme'");
        $this->addSql("UPDATE voting_platform_election_pool SET code = 'committee_supervisor' WHERE title = 'Animateurs locaux'");
        $this->addSql("UPDATE voting_platform_election_pool SET code = 'city_councilor' WHERE title = 'Conseillers municipaux'");
        $this->addSql("UPDATE voting_platform_election_pool SET code = 'elected_candidate_adherent' WHERE title = 'Adhérents désignés'");

        $this->addSql('ALTER TABLE voting_platform_election_pool CHANGE code code VARCHAR(255) NOT NULL');

        $this->addSql('ALTER TABLE voting_platform_candidate ADD additionally_elected TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE 
          voting_platform_election 
        ADD 
          additional_places SMALLINT UNSIGNED DEFAULT NULL, 
        ADD 
          additional_places_gender VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_candidate DROP additionally_elected');
        $this->addSql('ALTER TABLE voting_platform_election DROP additional_places, DROP additional_places_gender');
        $this->addSql('ALTER TABLE voting_platform_election_pool DROP code');
    }
}
