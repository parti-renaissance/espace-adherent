<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200206154041 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_proxies DROP FOREIGN KEY FK_9B5E777A35E47E35');
        $this->addSql('DROP INDEX IDX_9B5E777A35E47E35 ON procuration_proxies');
        $this->addSql('ALTER TABLE 
          procuration_proxies 
        DROP 
          referent_id, 
        DROP 
          invite_source_name, 
        DROP 
          invite_source_first_name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          procuration_proxies 
        ADD 
          referent_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          invite_source_name VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, 
        ADD 
          invite_source_first_name VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE 
          procuration_proxies 
        ADD 
          CONSTRAINT FK_9B5E777A35E47E35 FOREIGN KEY (referent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_9B5E777A35E47E35 ON procuration_proxies (referent_id)');
    }
}
