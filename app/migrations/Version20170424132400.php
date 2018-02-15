<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170424132400 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE legislative_district_zones ADD rank SMALLINT UNSIGNED NOT NULL DEFAULT 1');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE legislative_district_zones DROP rank');
    }
}
