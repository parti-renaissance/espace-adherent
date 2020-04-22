<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200422113357 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE designation (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          zones LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', 
          candidacy_start_date DATETIME NOT NULL, 
          candidacy_end_date DATETIME NOT NULL, 
          vote_start_date DATETIME NOT NULL, 
          vote_end_date DATETIME NOT NULL, 
          result_display_delay SMALLINT UNSIGNED NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE designation');
    }
}
