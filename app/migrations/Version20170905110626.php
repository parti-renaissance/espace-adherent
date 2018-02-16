<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170905110626 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE events DROP expert_assistance_description');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE events ADD expert_assistance_description VARCHAR(255) DEFAULT NULL');
    }
}
