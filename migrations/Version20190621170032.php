<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190621170032 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE application_request_running_mate ADD taken_for_city VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE application_request_volunteer ADD taken_for_city VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE application_request_running_mate DROP taken_for_city');
        $this->addSql('ALTER TABLE application_request_volunteer DROP taken_for_city');
    }
}
