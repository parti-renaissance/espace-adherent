<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20210106184835 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          voting_platform_candidate_group_result 
        ADD 
          total_mentions JSON DEFAULT NULL, 
        ADD 
          majority_mention VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_candidate_group_result DROP total_mentions, DROP majority_mention');
    }
}
