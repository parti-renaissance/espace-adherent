<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171217185611 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_projects ADD matched_skills TINYINT(1) DEFAULT \'0\' NOT NULL, ADD featured TINYINT(1) DEFAULT \'0\' NOT NULL, ADD admin_comment LONGTEXT DEFAULT NULL, CHANGE assistance_content assistance_content LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_projects DROP matched_skills, DROP featured, DROP admin_comment, CHANGE assistance_content assistance_content VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
