<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201120165911 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE jecoute_region (
          id INT AUTO_INCREMENT NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          name VARCHAR(255) NOT NULL, 
          code VARCHAR(255) NOT NULL, 
          subtitle VARCHAR(255) NOT NULL, 
          description LONGTEXT NOT NULL, 
          primary_color VARCHAR(255) NOT NULL, 
          external_link VARCHAR(255) DEFAULT NULL, 
          banner VARCHAR(255) DEFAULT NULL, 
          logo VARCHAR(255) NOT NULL, 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          canonical_name VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          UNIQUE INDEX UNIQ_4E74226F77153098 (code), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE jecoute_region');
    }
}
