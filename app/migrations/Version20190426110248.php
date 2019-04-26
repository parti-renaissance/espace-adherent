<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190426110248 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          assessor_requests CHANGE birth_city birth_city VARCHAR(255) NOT NULL, 
          CHANGE city city VARCHAR(255) NOT NULL, 
          CHANGE vote_city vote_city VARCHAR(255) NOT NULL, 
          CHANGE assessor_city assessor_city VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          assessor_requests CHANGE birth_city birth_city VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, 
          CHANGE city city VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, 
          CHANGE vote_city vote_city VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, 
          CHANGE assessor_city assessor_city VARCHAR(15) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
