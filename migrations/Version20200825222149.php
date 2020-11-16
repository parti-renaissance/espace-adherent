<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200825222149 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          territorial_council_election_poll_choice 
        ADD 
          uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');

        $this->addSql('UPDATE territorial_council_election_poll_choice SET uuid = UUID() WHERE uuid IS NULL');

        $this->addSql('ALTER TABLE 
          territorial_council_election_poll_choice 
        CHANGE 
          uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE territorial_council_election_poll_vote (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          choice_id INT UNSIGNED DEFAULT NULL, 
          membership_id INT UNSIGNED DEFAULT NULL, 
          created_at DATETIME NOT NULL, 
          INDEX IDX_BCDA0C15998666D1 (choice_id), 
          INDEX IDX_BCDA0C151FB354CD (membership_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          territorial_council_election_poll_vote 
        ADD 
          CONSTRAINT FK_BCDA0C15998666D1 FOREIGN KEY (choice_id) REFERENCES territorial_council_election_poll_choice (id)');
        $this->addSql('ALTER TABLE 
          territorial_council_election_poll_vote 
        ADD 
          CONSTRAINT FK_BCDA0C151FB354CD FOREIGN KEY (membership_id) REFERENCES territorial_council_membership (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_election_poll_choice DROP uuid');
        $this->addSql('DROP TABLE territorial_council_election_poll_vote');
    }
}
