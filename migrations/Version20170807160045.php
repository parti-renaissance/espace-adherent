<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170807160045 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE events SET type = \'event\' WHERE type = \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE events SET type = \'\' WHERE type = \'event\'');
    }
}
