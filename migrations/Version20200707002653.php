<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200707002653 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees ADD current_designation_id INT UNSIGNED DEFAULT NULL');

        $this->addSql('UPDATE committees AS c
        INNER JOIN committee_election AS ce ON ce.committee_id = c.id
        SET c.current_designation_id = ce.designation_id
        WHERE c.current_designation_id IS NULL');

        $this->addSql('ALTER TABLE 
          committees 
        ADD 
          CONSTRAINT FK_A36198C6B4D2A5D1 FOREIGN KEY (current_designation_id) REFERENCES designation (id)');
        $this->addSql('CREATE INDEX IDX_A36198C6B4D2A5D1 ON committees (current_designation_id)');
        $this->addSql('ALTER TABLE 
          committee_election 
        DROP 
          INDEX UNIQ_2CA406E5ED1A100B, 
        ADD 
          INDEX IDX_2CA406E5ED1A100B (committee_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          committee_election 
        DROP 
          INDEX IDX_2CA406E5ED1A100B, 
        ADD 
          UNIQUE INDEX UNIQ_2CA406E5ED1A100B (committee_id)');
        $this->addSql('ALTER TABLE committees DROP FOREIGN KEY FK_A36198C6B4D2A5D1');
        $this->addSql('DROP INDEX IDX_A36198C6B4D2A5D1 ON committees');
        $this->addSql('ALTER TABLE committees DROP current_designation_id');
    }
}
