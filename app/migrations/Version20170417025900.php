<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170417025900 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE facebook_profiles ADD has_auto_uploaded TINYINT(1) NOT NULL, ADD updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE facebook_profiles DROP has_auto_uploaded, DROP updated_at');
    }
}
