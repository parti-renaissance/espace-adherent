<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201126191138 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_4E74226F77153098 ON jecoute_region');
        $this->addSql('ALTER TABLE 
          jecoute_region 
        ADD 
          geo_region_id INT UNSIGNED NOT NULL, 
        DROP 
          name, 
        DROP 
          code, 
        DROP 
          canonical_name, 
        DROP 
          slug');
        $this->addSql('ALTER TABLE 
          jecoute_region 
        ADD 
          CONSTRAINT FK_4E74226F39192B5C FOREIGN KEY (geo_region_id) REFERENCES geo_region (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E74226F39192B5C ON jecoute_region (geo_region_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_region DROP FOREIGN KEY FK_4E74226F39192B5C');
        $this->addSql('DROP INDEX UNIQ_4E74226F39192B5C ON jecoute_region');
        $this->addSql('ALTER TABLE 
          jecoute_region 
        ADD 
          name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
        ADD 
          code VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
        ADD 
          canonical_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
        ADD 
          slug VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
        DROP 
          geo_region_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E74226F77153098 ON jecoute_region (code)');
    }
}
