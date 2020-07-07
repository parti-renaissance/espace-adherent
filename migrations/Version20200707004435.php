<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200707004435 extends AbstractMigration
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
        $this->addSql('ALTER TABLE committee_candidacy ADD committee_membership_id INT UNSIGNED NOT NULL');

        $this->addSql('UPDATE committee_candidacy AS t1
        INNER JOIN committees_memberships AS t2 ON t2.committee_candidacy_id = t1.id
        SET t1.committee_membership_id = t2.id');

        $this->addSql('ALTER TABLE 
          committee_candidacy 
        ADD 
          CONSTRAINT FK_9A04454FCC6DA91 FOREIGN KEY (committee_membership_id) REFERENCES committees_memberships (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9A04454FCC6DA91 ON committee_candidacy (committee_membership_id)');
        $this->addSql('ALTER TABLE committees_memberships DROP FOREIGN KEY FK_E7A6490E4F376ABC');
        $this->addSql('DROP INDEX UNIQ_E7A6490E4F376ABC ON committees_memberships');
        $this->addSql('ALTER TABLE committees_memberships DROP committee_candidacy_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          committee_election 
        DROP 
          INDEX IDX_2CA406E5ED1A100B, 
        ADD 
          UNIQUE INDEX UNIQ_2CA406E5ED1A100B (committee_id)');
        $this->addSql('ALTER TABLE committee_election CHANGE committee_id committee_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE committees DROP FOREIGN KEY FK_A36198C6B4D2A5D1');
        $this->addSql('DROP INDEX IDX_A36198C6B4D2A5D1 ON committees');
        $this->addSql('ALTER TABLE committees DROP current_designation_id');
        $this->addSql('ALTER TABLE committees_memberships ADD committee_candidacy_id INT DEFAULT NULL');

        $this->addSql('UPDATE committees_memberships AS t1
        INNER JOIN committee_candidacy AS t2 ON t2.committee_membership_id = t1.id
        SET t1.committee_candidacy_id = t2.id');

        $this->addSql('ALTER TABLE 
          committees_memberships
        ADD 
          CONSTRAINT FK_E7A6490E4F376ABC FOREIGN KEY (committee_candidacy_id) REFERENCES committee_candidacy (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E7A6490E4F376ABC ON committees_memberships (committee_candidacy_id)');

        $this->addSql('ALTER TABLE committee_candidacy DROP FOREIGN KEY FK_9A04454FCC6DA91');
        $this->addSql('DROP INDEX IDX_9A04454FCC6DA91 ON committee_candidacy');
        $this->addSql('ALTER TABLE committee_candidacy DROP committee_membership_id');
    }
}
