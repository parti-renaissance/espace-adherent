<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171127093926 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_projects ADD coordinator_comment LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE coordinator_managed_areas DROP INDEX UNIQ_C20973D25F06C53, ADD INDEX IDX_C20973D25F06C53 (adherent_id)');
        $this->addSql('ALTER TABLE coordinator_managed_areas ADD sector VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_projects DROP coordinator_comment');
        $this->addSql('ALTER TABLE coordinator_managed_areas DROP INDEX IDX_C20973D25F06C53, ADD UNIQUE INDEX UNIQ_C20973D25F06C53 (adherent_id)');
        $this->addSql('ALTER TABLE coordinator_managed_areas DROP sector');
    }
}
