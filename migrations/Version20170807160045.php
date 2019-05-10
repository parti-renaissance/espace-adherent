<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170807160045 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('UPDATE events SET type = \'event\' WHERE type = \'\'');
    }

    public function down(Schema $schema)
    {
        $this->addSql('UPDATE events SET type = \'\' WHERE type = \'event\'');
    }
}
