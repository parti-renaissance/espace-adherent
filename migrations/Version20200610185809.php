<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200610185809 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE 
          user_list_definition 
        ADD 
          color VARCHAR(7) DEFAULT NULL, 
          CHANGE code code VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE 
          user_list_definition 
        DROP 
          color, 
          CHANGE code code VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci');
    }
}
