<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200408160434 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          vote_result_list_collection 
        DROP 
          INDEX UNIQ_9C1DD9638BAC62AF, 
        ADD 
          INDEX IDX_9C1DD9638BAC62AF (city_id)');
        $this->addSql('ALTER TABLE vote_result_list_collection ADD election_round_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          vote_result_list_collection 
        ADD 
          CONSTRAINT FK_9C1DD963FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id)');
        $this->addSql('CREATE INDEX IDX_9C1DD963FCBF5E32 ON vote_result_list_collection (election_round_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          vote_result_list_collection 
        DROP 
          INDEX IDX_9C1DD9638BAC62AF, 
        ADD 
          UNIQUE INDEX UNIQ_9C1DD9638BAC62AF (city_id)');
        $this->addSql('ALTER TABLE vote_result_list_collection DROP FOREIGN KEY FK_9C1DD963FCBF5E32');
        $this->addSql('DROP INDEX IDX_9C1DD963FCBF5E32 ON vote_result_list_collection');
        $this->addSql('ALTER TABLE vote_result_list_collection DROP election_round_id');
    }
}
