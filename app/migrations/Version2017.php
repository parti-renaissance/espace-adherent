<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version2017 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $schemaExists = \count($this->sm->listTables()) > 1;

        $this->skipIf($schemaExists, 'Schema already set up');

        $this->addSql(file_get_contents(__DIR__.'/dump/dump-2017.sql'));
    }

    public function down(Schema $schema)
    {
    }
}
