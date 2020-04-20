<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200420091352 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE certification_request (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          status VARCHAR(20) NOT NULL, 
          document_name VARCHAR(255) DEFAULT NULL, 
          annotations JSON DEFAULT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          certification_request_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          certified_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3C518BF9E FOREIGN KEY (certification_request_id) REFERENCES certification_request (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3C518BF9E ON adherents (certification_request_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3C518BF9E');
        $this->addSql('DROP TABLE certification_request');
        $this->addSql('DROP INDEX UNIQ_562C7DA3C518BF9E ON adherents');
        $this->addSql('ALTER TABLE adherents DROP certification_request_id, DROP certified_at');
    }
}
