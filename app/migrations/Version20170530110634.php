<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170530110634 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE donations ADD duration SMALLINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE donations DROP duration');
    }
}
