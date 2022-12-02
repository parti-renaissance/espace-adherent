<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221202153430 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          designation
        ADD
          election_creation_date DATETIME DEFAULT NULL,
        ADD
          is_blank_vote_enabled TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_candidate ADD position SMALLINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE voting_platform_candidate_group ADD media_file_path VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation DROP election_creation_date, DROP is_blank_vote_enabled');
        $this->addSql('ALTER TABLE voting_platform_candidate DROP position');
        $this->addSql('ALTER TABLE voting_platform_candidate_group DROP media_file_path');
    }
}
