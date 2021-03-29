<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20210329151215 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE candidacies_group RENAME territorial_council_candidacies_group');

        $this->addSql('ALTER TABLE committee_candidacy DROP FOREIGN KEY FK_9A044548D4924C4');
        $this->addSql('DROP INDEX UNIQ_9A044548D4924C4 ON committee_candidacy');
        $this->addSql('ALTER TABLE committee_candidacy DROP binome_id');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B68D4924C4');
        $this->addSql('DROP INDEX UNIQ_39885B68D4924C4 ON territorial_council_candidacy');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP binome_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_candidacies_group RENAME candidacies_group');

        $this->addSql('ALTER TABLE committee_candidacy ADD binome_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          committee_candidacy 
        ADD 
          CONSTRAINT FK_9A044548D4924C4 FOREIGN KEY (binome_id) REFERENCES committee_candidacy (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9A044548D4924C4 ON committee_candidacy (binome_id)');
        $this->addSql('ALTER TABLE territorial_council_candidacy ADD binome_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B68D4924C4 FOREIGN KEY (binome_id) REFERENCES territorial_council_candidacy (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_39885B68D4924C4 ON territorial_council_candidacy (binome_id)');
    }
}
