<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201126141427 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE jecoute_news (
          id INT AUTO_INCREMENT NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          title VARCHAR(255) NOT NULL, 
          text LONGTEXT NOT NULL, 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE jecoute_news');
    }
}
