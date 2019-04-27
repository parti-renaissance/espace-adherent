<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180201171548 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE referent_managed_users_message DROP include_newsletter');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE referent_managed_users_message ADD include_newsletter TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
