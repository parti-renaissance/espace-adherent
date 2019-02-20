<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180824110646 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_projects DROP assistance_needed, DROP assistance_content');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_projects ADD assistance_needed TINYINT(1) DEFAULT \'0\' NOT NULL, ADD assistance_content LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
