<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171027123140 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE events ADD place VARCHAR(255) DEFAULT NULL, CHANGE begin_at begin_at DATETIME DEFAULT NULL, CHANGE finish_at finish_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE events DROP place, CHANGE begin_at begin_at DATETIME NOT NULL, CHANGE finish_at finish_at DATETIME NOT NULL');
    }
}
