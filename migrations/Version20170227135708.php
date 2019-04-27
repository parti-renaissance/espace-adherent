<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170227135708 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('DROP INDEX event_canonical_name_unique ON events');
    }

    public function down(Schema $schema)
    {
        $this->addSql('CREATE UNIQUE INDEX event_canonical_name_unique ON events (canonical_name)');
    }
}
