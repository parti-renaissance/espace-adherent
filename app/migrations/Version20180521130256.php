<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180521130256 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('UPDATE events SET finish_at = DATE_ADD(begin_at, INTERVAL 3 DAY) WHERE DATEDIFF(finish_at, begin_at) > 3');
    }

    public function down(Schema $schema)
    {
    }
}
