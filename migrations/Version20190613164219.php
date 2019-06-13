<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190613164219 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE application_request_running_mate CHANGE curriculum_name curriculum_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE application_request_running_mate CHANGE curriculum_name curriculum_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
