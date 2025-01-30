<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220412172359 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA39BF75CAD');
        $this->addSql('DROP INDEX UNIQ_562C7DA39BF75CAD ON adherents');
        $this->addSql('ALTER TABLE adherents DROP legislative_candidate_managed_district_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD legislative_candidate_managed_district_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA39BF75CAD FOREIGN KEY (
            legislative_candidate_managed_district_id
          ) REFERENCES districts (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA39BF75CAD ON adherents (
          legislative_candidate_managed_district_id
        )');
    }
}
