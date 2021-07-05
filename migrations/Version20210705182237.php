<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210705182237 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_council_candidacies_group ADD label VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE voting_platform_candidate_group ADD label VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_council_candidacies_group DROP label');
        $this->addSql('ALTER TABLE voting_platform_candidate_group DROP label');
    }
}
