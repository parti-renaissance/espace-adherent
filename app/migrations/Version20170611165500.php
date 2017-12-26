<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170611165500 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE legislative_candidates ADD status VARCHAR(20) DEFAULT \'none\' NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE legislative_candidates DROP status');
    }
}
