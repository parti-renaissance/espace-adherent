<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170612143204 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE home_blocks ADD display_titles TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE home_blocks DROP display_titles');
    }
}
