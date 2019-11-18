<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191118115449 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE programmatic_foundation_approach CHANGE content content LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE programmatic_foundation_sub_approach CHANGE content content LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_213A5F1E989D9B62 ON programmatic_foundation_measure');
        $this->addSql('ALTER TABLE programmatic_foundation_measure DROP slug');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          programmatic_foundation_approach CHANGE content content VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_measure 
        ADD 
          slug VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_213A5F1E989D9B62 ON programmatic_foundation_measure (slug)');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_sub_approach CHANGE content content VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
