<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170427194728 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE legislative_district_zones CHANGE rank rank SMALLINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE legislative_candidates ADD position INT NOT NULL, CHANGE gender gender VARCHAR(6) NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE legislative_candidates DROP position, CHANGE gender gender VARCHAR(6) DEFAULT \'male\' NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE legislative_district_zones CHANGE rank rank SMALLINT UNSIGNED DEFAULT 1 NOT NULL');
    }
}
