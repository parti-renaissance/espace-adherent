<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200901150326 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_candidate ADD faith_statement LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          voting_platform_election_entity 
        ADD 
          territorial_council_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          voting_platform_election_entity 
        ADD 
          CONSTRAINT FK_7AAD259FAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_7AAD259FAAA61A99 ON voting_platform_election_entity (territorial_council_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_candidate DROP faith_statement');
        $this->addSql('ALTER TABLE voting_platform_election_entity DROP FOREIGN KEY FK_7AAD259FAAA61A99');
        $this->addSql('DROP INDEX IDX_7AAD259FAAA61A99 ON voting_platform_election_entity');
        $this->addSql('ALTER TABLE voting_platform_election_entity DROP territorial_council_id');
    }
}
