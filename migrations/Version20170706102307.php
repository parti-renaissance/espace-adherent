<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170706102307 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE member_summary_mission_types ADD name VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX member_summary_mission_type_name_unique ON member_summary_mission_types (name)');
        $this->addSql('ALTER TABLE legislative_candidates CHANGE status status VARCHAR(20) DEFAULT \'none\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX member_summary_mission_type_name_unique ON member_summary_mission_types');
        $this->addSql('ALTER TABLE member_summary_mission_types DROP name');
        $this->addSql('ALTER TABLE legislative_candidates CHANGE status status VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci');
    }
}
