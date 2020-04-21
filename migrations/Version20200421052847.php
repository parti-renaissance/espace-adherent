<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200421052847 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE certification_request (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          adherent_id INT UNSIGNED NOT NULL, 
          processed_by_id INT DEFAULT NULL, 
          status VARCHAR(20) NOT NULL, 
          document_name VARCHAR(255) DEFAULT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          INDEX IDX_6E7481A925F06C53 (adherent_id), 
          INDEX IDX_6E7481A92FFD4FD3 (processed_by_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          certification_request 
        ADD 
          CONSTRAINT FK_6E7481A925F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          certification_request 
        ADD 
          CONSTRAINT FK_6E7481A92FFD4FD3 FOREIGN KEY (processed_by_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('ALTER TABLE adherents ADD certified_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE certification_request');
        $this->addSql('ALTER TABLE adherents DROP certified_at');
    }
}
