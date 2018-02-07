<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180207094434 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE adherents DROP com_email');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE adherents ADD com_email TINYINT(1) DEFAULT NULL');
    }
}
