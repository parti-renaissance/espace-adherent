<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200612142737 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE voting_platform_election_pool (
          id INT AUTO_INCREMENT NOT NULL, 
          election_id INT UNSIGNED DEFAULT NULL, 
          title VARCHAR(255) NOT NULL, 
          INDEX IDX_7225D6EFA708DAFF (election_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voting_platform_election_round (
          id INT AUTO_INCREMENT NOT NULL, 
          election_id INT UNSIGNED DEFAULT NULL, 
          is_active TINYINT(1) DEFAULT \'1\' NOT NULL, 
          INDEX IDX_F15D87B7A708DAFF (election_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voting_platform_election_round_election_pool (
          election_round_id INT NOT NULL, 
          election_pool_id INT NOT NULL, 
          INDEX IDX_E6665F19FCBF5E32 (election_round_id), 
          INDEX IDX_E6665F19C1E98F21 (election_pool_id), 
          PRIMARY KEY(
            election_round_id, election_pool_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          voting_platform_election_pool 
        ADD 
          CONSTRAINT FK_7225D6EFA708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_round 
        ADD 
          CONSTRAINT FK_F15D87B7A708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_round_election_pool 
        ADD 
          CONSTRAINT FK_E6665F19FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES voting_platform_election_round (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_round_election_pool 
        ADD 
          CONSTRAINT FK_E6665F19C1E98F21 FOREIGN KEY (election_pool_id) REFERENCES voting_platform_election_pool (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE voting_platform_vote DROP FOREIGN KEY FK_DCBB2B7BA708DAFF');
        $this->addSql('DROP INDEX IDX_DCBB2B7BA708DAFF ON voting_platform_vote');
        $this->addSql('DROP INDEX unique_vote ON voting_platform_vote');
        $this->addSql('ALTER TABLE voting_platform_vote ADD election_round_id INT DEFAULT NULL, DROP election_id');
        $this->addSql('ALTER TABLE 
          voting_platform_vote 
        ADD 
          CONSTRAINT FK_DCBB2B7BFCBF5E32 FOREIGN KEY (election_round_id) REFERENCES voting_platform_election_round (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_DCBB2B7BFCBF5E32 ON voting_platform_vote (election_round_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_vote ON voting_platform_vote (voter_id, election_round_id)');
        $this->addSql('ALTER TABLE voting_platform_candidate_group DROP FOREIGN KEY FK_2C1A353AA708DAFF');
        $this->addSql('DROP INDEX IDX_2C1A353AA708DAFF ON voting_platform_candidate_group');
        $this->addSql('ALTER TABLE 
          voting_platform_candidate_group 
        ADD 
          election_pool_id INT DEFAULT NULL, 
        DROP 
          election_id');
        $this->addSql('ALTER TABLE 
          voting_platform_candidate_group 
        ADD 
          CONSTRAINT FK_2C1A353AC1E98F21 FOREIGN KEY (election_pool_id) REFERENCES voting_platform_election_pool (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_2C1A353AC1E98F21 ON voting_platform_candidate_group (election_pool_id)');
        $this->addSql('ALTER TABLE voting_platform_vote_result DROP FOREIGN KEY FK_62C86890A708DAFF');
        $this->addSql('DROP INDEX IDX_62C86890A708DAFF ON voting_platform_vote_result');
        $this->addSql('DROP INDEX unique_vote ON voting_platform_vote_result');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_result 
        ADD 
          election_round_id INT DEFAULT NULL, 
        DROP 
          election_id');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_result 
        ADD 
          CONSTRAINT FK_62C86890FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES voting_platform_election_round (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_62C86890FCBF5E32 ON voting_platform_vote_result (election_round_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_vote ON voting_platform_vote_result (voter_key, election_round_id)');
        $this->addSql('ALTER TABLE designation ADD additional_round_duration SMALLINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_vote_choice ADD election_pool_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_choice 
        ADD 
          CONSTRAINT FK_B009F311C1E98F21 FOREIGN KEY (election_pool_id) REFERENCES voting_platform_election_pool (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B009F311C1E98F21 ON voting_platform_vote_choice (election_pool_id)');
        $this->addSql('ALTER TABLE 
          voting_platform_election 
        ADD 
          status VARCHAR(255) NOT NULL, 
        ADD 
          closed_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_candidate_group DROP FOREIGN KEY FK_2C1A353AC1E98F21');
        $this->addSql('ALTER TABLE voting_platform_election_round_election_pool DROP FOREIGN KEY FK_E6665F19C1E98F21');
        $this->addSql('ALTER TABLE voting_platform_vote_choice DROP FOREIGN KEY FK_B009F311C1E98F21');
        $this->addSql('ALTER TABLE voting_platform_vote DROP FOREIGN KEY FK_DCBB2B7BFCBF5E32');
        $this->addSql('ALTER TABLE voting_platform_vote_result DROP FOREIGN KEY FK_62C86890FCBF5E32');
        $this->addSql('ALTER TABLE voting_platform_election_round_election_pool DROP FOREIGN KEY FK_E6665F19FCBF5E32');
        $this->addSql('DROP TABLE voting_platform_election_pool');
        $this->addSql('DROP TABLE voting_platform_election_round');
        $this->addSql('DROP TABLE voting_platform_election_round_election_pool');
        $this->addSql('ALTER TABLE designation DROP additional_round_duration');
        $this->addSql('DROP INDEX IDX_2C1A353AC1E98F21 ON voting_platform_candidate_group');
        $this->addSql('ALTER TABLE 
          voting_platform_candidate_group 
        ADD 
          election_id INT UNSIGNED DEFAULT NULL, 
        DROP 
          election_pool_id');
        $this->addSql('ALTER TABLE 
          voting_platform_candidate_group 
        ADD 
          CONSTRAINT FK_2C1A353AA708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_2C1A353AA708DAFF ON voting_platform_candidate_group (election_id)');
        $this->addSql('ALTER TABLE voting_platform_election DROP status, DROP closed_at');
        $this->addSql('DROP INDEX IDX_DCBB2B7BFCBF5E32 ON voting_platform_vote');
        $this->addSql('DROP INDEX unique_vote ON voting_platform_vote');
        $this->addSql('ALTER TABLE 
          voting_platform_vote 
        ADD 
          election_id INT UNSIGNED DEFAULT NULL, 
        DROP 
          election_round_id');
        $this->addSql('ALTER TABLE 
          voting_platform_vote 
        ADD 
          CONSTRAINT FK_DCBB2B7BA708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_DCBB2B7BA708DAFF ON voting_platform_vote (election_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_vote ON voting_platform_vote (voter_id, election_id)');
        $this->addSql('DROP INDEX IDX_B009F311C1E98F21 ON voting_platform_vote_choice');
        $this->addSql('ALTER TABLE voting_platform_vote_choice DROP election_pool_id');
        $this->addSql('DROP INDEX IDX_62C86890FCBF5E32 ON voting_platform_vote_result');
        $this->addSql('DROP INDEX unique_vote ON voting_platform_vote_result');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_result 
        ADD 
          election_id INT UNSIGNED DEFAULT NULL, 
        DROP 
          election_round_id');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_result 
        ADD 
          CONSTRAINT FK_62C86890A708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_62C86890A708DAFF ON voting_platform_vote_result (election_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_vote ON voting_platform_vote_result (voter_key, election_id)');
    }
}
