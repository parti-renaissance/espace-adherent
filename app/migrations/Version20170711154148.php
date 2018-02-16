<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170711154148 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE home_blocks ADD display_block TINYINT(1) DEFAULT \'1\' NOT NULL, ADD title_cta VARCHAR(70) DEFAULT NULL, ADD color_cta VARCHAR(6) DEFAULT NULL, CHANGE position position SMALLINT NOT NULL, CHANGE position_name position_name VARCHAR(30) NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE home_blocks DROP display_block, DROP title_cta, DROP color_cta, CHANGE position position VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci, CHANGE position_name position_name VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci');
    }
}
