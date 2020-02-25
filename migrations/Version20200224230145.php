<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200224230145 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          cities 
        ADD 
          postal_codes LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', 
        DROP 
          postal_code');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          cities 
        ADD 
          postal_code VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, 
        DROP 
          postal_codes');
    }
}
