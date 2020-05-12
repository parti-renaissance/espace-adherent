<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200511124432 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_election ADD designation_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          committee_election 
        ADD 
          CONSTRAINT FK_2CA406E5FAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id)');
        $this->addSql('CREATE INDEX IDX_2CA406E5FAC7D83F ON committee_election (designation_id)');
        $this->addSql('ALTER TABLE voting_platform_election_entity ADD election_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          voting_platform_election_entity 
        ADD 
          CONSTRAINT FK_7AAD259FA708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7AAD259FA708DAFF ON voting_platform_election_entity (election_id)');
        $this->addSql('ALTER TABLE voting_platform_election DROP FOREIGN KEY FK_4E144C949F7E3037');
        $this->addSql('DROP INDEX UNIQ_4E144C949F7E3037 ON voting_platform_election');
        $this->addSql('ALTER TABLE 
          voting_platform_election 
        ADD 
          designation_id INT UNSIGNED DEFAULT NULL, 
        DROP 
          election_entity_id, 
        DROP 
          title, 
        DROP 
          start_date, 
        DROP 
          end_date, 
        DROP 
          designation_type');
        $this->addSql('ALTER TABLE 
          voting_platform_election 
        ADD 
          CONSTRAINT FK_4E144C94FAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id)');
        $this->addSql('CREATE INDEX IDX_4E144C94FAC7D83F ON voting_platform_election (designation_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_election DROP FOREIGN KEY FK_2CA406E5FAC7D83F');
        $this->addSql('DROP INDEX IDX_2CA406E5FAC7D83F ON committee_election');
        $this->addSql('ALTER TABLE committee_election DROP designation_id');
        $this->addSql('ALTER TABLE voting_platform_election DROP FOREIGN KEY FK_4E144C94FAC7D83F');
        $this->addSql('DROP INDEX IDX_4E144C94FAC7D83F ON voting_platform_election');
        $this->addSql('ALTER TABLE 
          voting_platform_election 
        ADD 
          election_entity_id INT DEFAULT NULL, 
        ADD 
          title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
        ADD 
          start_date DATETIME NOT NULL, 
        ADD 
          end_date DATETIME NOT NULL, 
        ADD 
          designation_type VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
        DROP 
          designation_id');
        $this->addSql('ALTER TABLE 
          voting_platform_election 
        ADD 
          CONSTRAINT FK_4E144C949F7E3037 FOREIGN KEY (election_entity_id) REFERENCES voting_platform_election_entity (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E144C949F7E3037 ON voting_platform_election (election_entity_id)');
        $this->addSql('ALTER TABLE voting_platform_election_entity DROP FOREIGN KEY FK_7AAD259FA708DAFF');
        $this->addSql('DROP INDEX UNIQ_7AAD259FA708DAFF ON voting_platform_election_entity');
        $this->addSql('ALTER TABLE voting_platform_election_entity DROP election_id');
    }
}
