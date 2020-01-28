<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200128150400 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE consular_districts RENAME consular_district');
        $this->addSql('ALTER TABLE 
          consular_district 
        ADD 
          number SMALLINT NOT NULL, 
        ADD 
          points JSON DEFAULT NULL, 
          CHANGE code code VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX consular_district_code_unique ON consular_district (code)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX consular_district_code_unique ON consular_district');
        $this->addSql('ALTER TABLE 
          consular_district 
        DROP 
          number, 
        DROP 
          points, 
          CHANGE code code VARCHAR(6) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE consular_district RENAME consular_districts');
    }
}
