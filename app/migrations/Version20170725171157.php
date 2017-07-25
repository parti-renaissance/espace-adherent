<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170725171157 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('DROP TABLE member_summary_skills');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE summary_skills');
    }
}
