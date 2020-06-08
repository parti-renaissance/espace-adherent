<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200608142421 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          certification_request 
        ADD 
          found_duplicated_adherent_id INT UNSIGNED DEFAULT NULL, 
          CHANGE document_name document_name VARCHAR(255) DEFAULT NULL, 
          CHANGE document_mime_type document_mime_type VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          certification_request 
        ADD 
          CONSTRAINT FK_6E7481A96EA98020 FOREIGN KEY (found_duplicated_adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_6E7481A96EA98020 ON certification_request (found_duplicated_adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE certification_request DROP FOREIGN KEY FK_6E7481A96EA98020');
        $this->addSql('DROP INDEX IDX_6E7481A96EA98020 ON certification_request');
        $this->addSql('ALTER TABLE 
          certification_request 
        DROP 
          found_duplicated_adherent_id, 
          CHANGE document_name document_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
          CHANGE document_mime_type document_mime_type VARCHAR(30) NOT NULL COLLATE utf8_unicode_ci');
    }
}
