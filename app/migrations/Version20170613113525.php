<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170613113525 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE home_blocks CHANGE display_titles display_titles TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE legislative_candidates CHANGE status status VARCHAR(20) DEFAULT \'none\' NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE home_blocks CHANGE display_titles display_titles TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE legislative_candidates CHANGE status status VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci');
    }
}
