<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170626174545 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE member_summary_job_experiences CHANGE started_at started_at DATE NOT NULL');
        $this->addSql('ALTER TABLE member_summary_trainings CHANGE ended_at ended_at DATE DEFAULT NULL, CHANGE extracurricular extra_curricular LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE summaries CHANGE availabilities availabilities LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE job_locations job_locations LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE public public TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE showing_recent_activities showing_recent_activities TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE member_summary_job_experiences CHANGE started_at started_at DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE member_summary_trainings CHANGE ended_at ended_at DATE NOT NULL, CHANGE extra_curricular extracurricular LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE summaries CHANGE availabilities availabilities LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\', CHANGE job_locations job_locations LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\', CHANGE showing_recent_activities showing_recent_activities SMALLINT DEFAULT 0 NOT NULL, CHANGE public public SMALLINT DEFAULT 0 NOT NULL');
    }
}
