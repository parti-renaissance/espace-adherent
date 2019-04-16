<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190415111008 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          assessor_requests CHANGE assessor_city assessor_city VARCHAR(15) DEFAULT NULL, 
          CHANGE assessor_postal_code assessor_postal_code VARCHAR(15) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          assessor_requests CHANGE assessor_city assessor_city VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, 
          CHANGE assessor_postal_code assessor_postal_code VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci');
    }
}
