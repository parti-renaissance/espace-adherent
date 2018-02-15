<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171017115955 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('UPDATE committees_memberships LEFT JOIN committees ON committees.uuid = committees_memberships.committee_uuid  SET privilege = \'FOLLOWER\' WHERE committees.status != \'APPROVED\'');
    }

    public function down(Schema $schema)
    {
    }
}
