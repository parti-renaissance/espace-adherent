<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200714020357 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE voting_platform_election_pool_result (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          election_pool_id INT DEFAULT NULL, 
          election_round_result_id INT UNSIGNED DEFAULT NULL, 
          is_elected TINYINT(1) DEFAULT \'0\' NOT NULL, 
          expressed INT UNSIGNED DEFAULT 0 NOT NULL, 
          blank INT UNSIGNED DEFAULT 0 NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          INDEX IDX_13C1C73FC1E98F21 (election_pool_id), 
          INDEX IDX_13C1C73F8FFC0F0B (election_round_result_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voting_platform_election_result (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          election_id INT UNSIGNED DEFAULT NULL, 
          participated INT UNSIGNED DEFAULT 0 NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          UNIQUE INDEX UNIQ_67EFA0E4A708DAFF (election_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voting_platform_candidate_group_result (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          candidate_group_id INT UNSIGNED DEFAULT NULL, 
          election_pool_result_id INT UNSIGNED DEFAULT NULL, 
          total INT UNSIGNED DEFAULT 0 NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          INDEX IDX_7249D5375F0A9B94 (candidate_group_id), 
          INDEX IDX_7249D537B5BA5CC5 (election_pool_result_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voting_platform_election_round_result (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          election_round_id INT DEFAULT NULL, 
          election_result_id INT UNSIGNED DEFAULT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          UNIQUE INDEX UNIQ_F2670966FCBF5E32 (election_round_id), 
          INDEX IDX_F267096619FCFB29 (election_result_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          voting_platform_election_pool_result 
        ADD 
          CONSTRAINT FK_13C1C73FC1E98F21 FOREIGN KEY (election_pool_id) REFERENCES voting_platform_election_pool (id)');
        $this->addSql('ALTER TABLE 
          voting_platform_election_pool_result 
        ADD 
          CONSTRAINT FK_13C1C73F8FFC0F0B FOREIGN KEY (election_round_result_id) REFERENCES voting_platform_election_round_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_result 
        ADD 
          CONSTRAINT FK_67EFA0E4A708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id)');
        $this->addSql('ALTER TABLE 
          voting_platform_candidate_group_result 
        ADD 
          CONSTRAINT FK_7249D5375F0A9B94 FOREIGN KEY (candidate_group_id) REFERENCES voting_platform_candidate_group (id)');
        $this->addSql('ALTER TABLE 
          voting_platform_candidate_group_result 
        ADD 
          CONSTRAINT FK_7249D537B5BA5CC5 FOREIGN KEY (election_pool_result_id) REFERENCES voting_platform_election_pool_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_round_result 
        ADD 
          CONSTRAINT FK_F2670966FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES voting_platform_election_round (id)');
        $this->addSql('ALTER TABLE 
          voting_platform_election_round_result 
        ADD 
          CONSTRAINT FK_F267096619FCFB29 FOREIGN KEY (election_result_id) REFERENCES voting_platform_election_result (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_candidate_group_result DROP FOREIGN KEY FK_7249D537B5BA5CC5');
        $this->addSql('ALTER TABLE voting_platform_election_round_result DROP FOREIGN KEY FK_F267096619FCFB29');
        $this->addSql('ALTER TABLE voting_platform_election_pool_result DROP FOREIGN KEY FK_13C1C73F8FFC0F0B');
        $this->addSql('DROP TABLE voting_platform_election_pool_result');
        $this->addSql('DROP TABLE voting_platform_election_result');
        $this->addSql('DROP TABLE voting_platform_candidate_group_result');
        $this->addSql('DROP TABLE voting_platform_election_round_result');
    }
}
