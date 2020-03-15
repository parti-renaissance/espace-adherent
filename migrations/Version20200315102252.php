<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200315102252 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          ministry_list_total_result 
        ADD 
          position INT DEFAULT NULL, 
        ADD 
          candidate_first_name VARCHAR(255) DEFAULT NULL, 
        ADD 
          candidate_last_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          vote_result_list 
        ADD 
          position INT DEFAULT NULL, 
        ADD 
          candidate_first_name VARCHAR(255) DEFAULT NULL, 
        ADD 
          candidate_last_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          ministry_list_total_result 
        DROP 
          position, 
        DROP 
          candidate_first_name, 
        DROP 
          candidate_last_name');
        $this->addSql('ALTER TABLE 
          vote_result_list 
        DROP 
          position, 
        DROP 
          candidate_first_name, 
        DROP 
          candidate_last_name');
    }
}
