<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200825122458 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE territorial_council_election_poll (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE territorial_council_election_poll_choice (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          election_poll_id INT UNSIGNED NOT NULL, 
          value VARCHAR(255) NOT NULL, 
          INDEX IDX_63EBCF6B8649F5F1 (election_poll_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          territorial_council_election_poll_choice 
        ADD 
          CONSTRAINT FK_63EBCF6B8649F5F1 FOREIGN KEY (election_poll_id) REFERENCES territorial_council_election_poll (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          territorial_council_election 
        ADD 
          election_poll_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          meeting_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          territorial_council_election 
        ADD 
          CONSTRAINT FK_14CBC36B8649F5F1 FOREIGN KEY (election_poll_id) REFERENCES territorial_council_election_poll (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_14CBC36B8649F5F1 ON territorial_council_election (election_poll_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_election_poll_choice DROP FOREIGN KEY FK_63EBCF6B8649F5F1');
        $this->addSql('ALTER TABLE territorial_council_election DROP FOREIGN KEY FK_14CBC36B8649F5F1');
        $this->addSql('DROP TABLE territorial_council_election_poll');
        $this->addSql('DROP TABLE territorial_council_election_poll_choice');
        $this->addSql('DROP INDEX UNIQ_14CBC36B8649F5F1 ON territorial_council_election');
        $this->addSql('ALTER TABLE territorial_council_election DROP election_poll_id, DROP meeting_url');
    }
}
