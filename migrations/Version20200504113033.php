<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200504113033 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE voting_platform_election (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          election_entity_id INT DEFAULT NULL, 
          title VARCHAR(255) NOT NULL, 
          start_date DATETIME NOT NULL, 
          end_date DATETIME NOT NULL, 
          designation_type VARCHAR(255) NOT NULL, 
          UNIQUE INDEX UNIQ_4E144C949F7E3037 (election_entity_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voting_platform_voter (
          id INT AUTO_INCREMENT NOT NULL, 
          adherent_id INT UNSIGNED DEFAULT NULL, 
          created_at DATETIME NOT NULL, 
          UNIQUE INDEX UNIQ_AB02EC0225F06C53 (adherent_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voting_platform_voters_list (
          id INT AUTO_INCREMENT NOT NULL, 
          election_id INT UNSIGNED DEFAULT NULL, 
          UNIQUE INDEX UNIQ_3C73500DA708DAFF (election_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voting_platform_voters_list_voter (
          voters_list_id INT NOT NULL, 
          voter_id INT NOT NULL, 
          INDEX IDX_7CC26956FB0C8C84 (voters_list_id), 
          INDEX IDX_7CC26956EBB4B8AD (voter_id), 
          PRIMARY KEY(voters_list_id, voter_id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voting_platform_election_entity (
          id INT AUTO_INCREMENT NOT NULL, 
          committee_id INT UNSIGNED DEFAULT NULL, 
          INDEX IDX_7AAD259FED1A100B (committee_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voting_platform_vote (
          id INT AUTO_INCREMENT NOT NULL, 
          voter_id INT DEFAULT NULL, 
          election_id INT UNSIGNED DEFAULT NULL, 
          voted_at DATETIME NOT NULL, 
          INDEX IDX_DCBB2B7BEBB4B8AD (voter_id), 
          INDEX IDX_DCBB2B7BA708DAFF (election_id), 
          UNIQUE INDEX unique_vote (voter_id, election_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voting_platform_vote_candidate_group (
          vote_id INT NOT NULL, 
          candidate_group_id INT UNSIGNED NOT NULL, 
          INDEX IDX_AFB0733772DCDAFC (vote_id), 
          INDEX IDX_AFB073375F0A9B94 (candidate_group_id), 
          PRIMARY KEY(vote_id, candidate_group_id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voting_platform_candidate_group (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          election_id INT UNSIGNED DEFAULT NULL, 
          INDEX IDX_2C1A353AA708DAFF (election_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voting_platform_candidate (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          candidate_group_id INT UNSIGNED DEFAULT NULL, 
          first_name VARCHAR(255) NOT NULL, 
          last_name VARCHAR(255) NOT NULL, 
          gender VARCHAR(255) NOT NULL, 
          biography LONGTEXT DEFAULT NULL, 
          image_path VARCHAR(255) DEFAULT NULL, 
          INDEX IDX_3F426D6D5F0A9B94 (candidate_group_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          voting_platform_election 
        ADD 
          CONSTRAINT FK_4E144C949F7E3037 FOREIGN KEY (election_entity_id) REFERENCES voting_platform_election_entity (id)');
        $this->addSql('ALTER TABLE 
          voting_platform_voter 
        ADD 
          CONSTRAINT FK_AB02EC0225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_voters_list 
        ADD 
          CONSTRAINT FK_3C73500DA708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_voters_list_voter 
        ADD 
          CONSTRAINT FK_7CC26956FB0C8C84 FOREIGN KEY (voters_list_id) REFERENCES voting_platform_voters_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_voters_list_voter 
        ADD 
          CONSTRAINT FK_7CC26956EBB4B8AD FOREIGN KEY (voter_id) REFERENCES voting_platform_voter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_entity 
        ADD 
          CONSTRAINT FK_7AAD259FED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('ALTER TABLE 
          voting_platform_vote 
        ADD 
          CONSTRAINT FK_DCBB2B7BEBB4B8AD FOREIGN KEY (voter_id) REFERENCES voting_platform_voter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_vote 
        ADD 
          CONSTRAINT FK_DCBB2B7BA708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_candidate_group 
        ADD 
          CONSTRAINT FK_AFB0733772DCDAFC FOREIGN KEY (vote_id) REFERENCES voting_platform_vote (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_candidate_group 
        ADD 
          CONSTRAINT FK_AFB073375F0A9B94 FOREIGN KEY (candidate_group_id) REFERENCES voting_platform_candidate_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_candidate_group 
        ADD 
          CONSTRAINT FK_2C1A353AA708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          voting_platform_candidate 
        ADD 
          CONSTRAINT FK_3F426D6D5F0A9B94 FOREIGN KEY (candidate_group_id) REFERENCES voting_platform_candidate_group (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_voters_list DROP FOREIGN KEY FK_3C73500DA708DAFF');
        $this->addSql('ALTER TABLE voting_platform_vote DROP FOREIGN KEY FK_DCBB2B7BA708DAFF');
        $this->addSql('ALTER TABLE voting_platform_candidate_group DROP FOREIGN KEY FK_2C1A353AA708DAFF');
        $this->addSql('ALTER TABLE voting_platform_voters_list_voter DROP FOREIGN KEY FK_7CC26956EBB4B8AD');
        $this->addSql('ALTER TABLE voting_platform_vote DROP FOREIGN KEY FK_DCBB2B7BEBB4B8AD');
        $this->addSql('ALTER TABLE voting_platform_voters_list_voter DROP FOREIGN KEY FK_7CC26956FB0C8C84');
        $this->addSql('ALTER TABLE voting_platform_election DROP FOREIGN KEY FK_4E144C949F7E3037');
        $this->addSql('ALTER TABLE voting_platform_vote_candidate_group DROP FOREIGN KEY FK_AFB0733772DCDAFC');
        $this->addSql('ALTER TABLE voting_platform_vote_candidate_group DROP FOREIGN KEY FK_AFB073375F0A9B94');
        $this->addSql('ALTER TABLE voting_platform_candidate DROP FOREIGN KEY FK_3F426D6D5F0A9B94');
        $this->addSql('DROP TABLE voting_platform_election');
        $this->addSql('DROP TABLE voting_platform_voter');
        $this->addSql('DROP TABLE voting_platform_voters_list');
        $this->addSql('DROP TABLE voting_platform_voters_list_voter');
        $this->addSql('DROP TABLE voting_platform_election_entity');
        $this->addSql('DROP TABLE voting_platform_vote');
        $this->addSql('DROP TABLE voting_platform_vote_candidate_group');
        $this->addSql('DROP TABLE voting_platform_candidate_group');
        $this->addSql('DROP TABLE voting_platform_candidate');
    }
}
