<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170529215440 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE adherents ADD com_mobile TINYINT(1) DEFAULT NULL, ADD com_email TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE adherents DROP com_mobile, DROP com_email');
    }
}
