<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200810225900 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_candidacy ADD binome_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B68D4924C4 FOREIGN KEY (binome_id) REFERENCES territorial_council_candidacy (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_39885B68D4924C4 ON territorial_council_candidacy (binome_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B68D4924C4');
        $this->addSql('DROP INDEX UNIQ_39885B68D4924C4 ON territorial_council_candidacy');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP binome_id');
    }
}
