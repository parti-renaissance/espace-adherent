<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180201171548 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_managed_users_message DROP include_newsletter');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_managed_users_message ADD include_newsletter TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
