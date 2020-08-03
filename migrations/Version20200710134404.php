<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200710134404 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mailchimp_segment (
          id INT AUTO_INCREMENT NOT NULL, 
          list VARCHAR(255) NOT NULL, 
          label VARCHAR(255) NOT NULL, 
          external_id VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE mailchimp_segment');
    }
}
