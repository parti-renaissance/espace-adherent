<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200507151000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE voting_platform_vote_result (
          id INT AUTO_INCREMENT NOT NULL, 
          election_id INT UNSIGNED DEFAULT NULL, 
          voter_key VARCHAR(255) NOT NULL, 
          voted_at DATETIME NOT NULL, 
          INDEX IDX_62C86890A708DAFF (election_id), 
          UNIQUE INDEX unique_vote (voter_key, election_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voting_platform_vote_choice (
          id INT AUTO_INCREMENT NOT NULL, 
          vote_result_id INT DEFAULT NULL, 
          candidate_group_id INT UNSIGNED DEFAULT NULL, 
          is_blank TINYINT(1) DEFAULT \'0\' NOT NULL, 
          INDEX IDX_B009F31145EB7186 (vote_result_id), 
          INDEX IDX_B009F3115F0A9B94 (candidate_group_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_result 
        ADD 
          CONSTRAINT FK_62C86890A708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_choice 
        ADD 
          CONSTRAINT FK_B009F31145EB7186 FOREIGN KEY (vote_result_id) REFERENCES voting_platform_vote_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_choice 
        ADD 
          CONSTRAINT FK_B009F3115F0A9B94 FOREIGN KEY (candidate_group_id) REFERENCES voting_platform_candidate_group (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE voting_platform_vote_candidate_group');
        $this->addSql('ALTER TABLE voting_platform_voter DROP FOREIGN KEY FK_AB02EC0225F06C53');
        $this->addSql('ALTER TABLE 
          voting_platform_voter 
        ADD 
          CONSTRAINT FK_AB02EC0225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_vote_choice DROP FOREIGN KEY FK_B009F31145EB7186');
        $this->addSql('CREATE TABLE voting_platform_vote_candidate_group (
          vote_id INT NOT NULL, 
          candidate_group_id INT UNSIGNED NOT NULL, 
          INDEX IDX_AFB0733772DCDAFC (vote_id), 
          INDEX IDX_AFB073375F0A9B94 (candidate_group_id), 
          PRIMARY KEY(vote_id, candidate_group_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_candidate_group 
        ADD 
          CONSTRAINT FK_AFB073375F0A9B94 FOREIGN KEY (candidate_group_id) REFERENCES voting_platform_candidate_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_candidate_group 
        ADD 
          CONSTRAINT FK_AFB0733772DCDAFC FOREIGN KEY (vote_id) REFERENCES voting_platform_vote (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE voting_platform_vote_result');
        $this->addSql('DROP TABLE voting_platform_vote_choice');
        $this->addSql('ALTER TABLE voting_platform_voter DROP FOREIGN KEY FK_AB02EC0225F06C53');
        $this->addSql('ALTER TABLE 
          voting_platform_voter 
        ADD 
          CONSTRAINT FK_AB02EC0225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }
}
