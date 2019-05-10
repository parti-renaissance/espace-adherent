<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170728084630 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE home_blocks ADD video_controls TINYINT(1) DEFAULT \'0\' NOT NULL, ADD video_autoplay_loop TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE home_blocks DROP video_controls, DROP video_autoplay_loop');
    }
}
