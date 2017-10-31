<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171031135117 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('INSERT INTO roles(code,name) VALUES (\'personality\', \'Personnalité\')');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DELETE FROM roles WHERE code = \'personality\'');
    }
}
