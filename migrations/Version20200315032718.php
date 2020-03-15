<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200315032718 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          election_city_contact 
        ADD 
          name VARCHAR(255) NOT NULL, 
        DROP 
          first_name, 
        DROP 
          last_name');
        $this->addSql('ALTER TABLE 
          election_city_candidate 
        ADD 
          name VARCHAR(255) NOT NULL, 
        DROP 
          first_name, 
        DROP 
          last_name');
        $this->addSql('ALTER TABLE 
          election_city_prevision 
        ADD 
          name VARCHAR(255) DEFAULT NULL, 
        DROP 
          first_name, 
        DROP 
          last_name');
        $this->addSql('ALTER TABLE 
          election_city_manager 
        ADD 
          name VARCHAR(255) NOT NULL, 
        DROP 
          first_name, 
        DROP 
          last_name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          election_city_candidate 
        ADD 
          last_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
          CHANGE name first_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE 
          election_city_contact 
        ADD 
          last_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
          CHANGE name first_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE 
          election_city_manager 
        ADD 
          last_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
          CHANGE name first_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE 
          election_city_prevision 
        ADD 
          last_name VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          CHANGE name first_name VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
