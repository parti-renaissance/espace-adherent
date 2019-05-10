<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180308143058 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE articles_categories ADD display TINYINT(1) NOT NULL DEFAULT 1');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE articles_categories DROP display');
    }
}
