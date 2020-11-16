<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200827233545 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          legislative_candidate_managed_district_id INT UNSIGNED DEFAULT NULL, 
        DROP 
          legislative_candidate');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA39BF75CAD FOREIGN KEY (
            legislative_candidate_managed_district_id
          ) REFERENCES districts (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA39BF75CAD ON adherents (
          legislative_candidate_managed_district_id
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA39BF75CAD');
        $this->addSql('DROP INDEX UNIQ_562C7DA39BF75CAD ON adherents');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          legislative_candidate TINYINT(1) DEFAULT \'0\' NOT NULL, 
        DROP 
          legislative_candidate_managed_district_id');
    }
}
