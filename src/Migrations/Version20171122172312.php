<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171122172312 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE adherents ADD citizen_project_creation_email_subscription_radius INT DEFAULT 10 NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE adherents DROP citizen_project_creation_email_subscription_radius');
    }
}
