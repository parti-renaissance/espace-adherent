<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170807164446 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('UPDATE events SET type = \'citizen_initiative\' WHERE type = \'initiative\'');
    }

    public function down(Schema $schema)
    {
        $this->addSql('UPDATE events SET type = \'initiative\' WHERE type = \'citizen_initiative\'');
    }
}
