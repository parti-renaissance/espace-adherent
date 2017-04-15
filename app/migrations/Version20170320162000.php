<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170320162000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("UPDATE `committees_memberships` SET `privilege` = 'SUPERVISOR' WHERE `privilege` = 'HOST'");
    }

    public function down(Schema $schema)
    {
        $this->addSql("UPDATE `committees_memberships` SET `privilege` = 'HOST' WHERE `privilege` = 'SUPERVISOR'");
    }
}
