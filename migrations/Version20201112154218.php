<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201112154218 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE filesystem_file (id INT UNSIGNED AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, parent_id INT UNSIGNED DEFAULT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(255) NOT NULL, type VARCHAR(20) NOT NULL, displayed TINYINT(1) DEFAULT \'1\' NOT NULL, original_filename VARCHAR(255) DEFAULT NULL, extension VARCHAR(10) DEFAULT NULL, mime_type VARCHAR(75) DEFAULT NULL, size INT UNSIGNED DEFAULT NULL, external_link VARCHAR(255) DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_47F0AE28B03A8386 (created_by_id), INDEX IDX_47F0AE28896DBBDE (updated_by_id), INDEX IDX_47F0AE28727ACA70 (parent_id), INDEX IDX_47F0AE288CDE5729 (type), INDEX IDX_47F0AE285E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filesystem_file_permission (id INT UNSIGNED AUTO_INCREMENT NOT NULL, file_id INT UNSIGNED NOT NULL, name VARCHAR(50) NOT NULL, INDEX IDX_BD623E4C93CB796C (file_id), UNIQUE INDEX file_permission_unique (file_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE filesystem_file ADD CONSTRAINT FK_47F0AE28B03A8386 FOREIGN KEY (created_by_id) REFERENCES administrators (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE filesystem_file ADD CONSTRAINT FK_47F0AE28896DBBDE FOREIGN KEY (updated_by_id) REFERENCES administrators (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE filesystem_file ADD CONSTRAINT FK_47F0AE28727ACA70 FOREIGN KEY (parent_id) REFERENCES filesystem_file (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX filesystem_file_slug_unique ON filesystem_file (slug)');
        $this->addSql('ALTER TABLE filesystem_file_permission ADD CONSTRAINT FK_BD623E4C93CB796C FOREIGN KEY (file_id) REFERENCES filesystem_file (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX filesystem_file_slug_unique ON filesystem_file');
        $this->addSql('ALTER TABLE filesystem_file DROP FOREIGN KEY FK_47F0AE28727ACA70');
        $this->addSql('ALTER TABLE filesystem_file_permission DROP FOREIGN KEY FK_BD623E4C93CB796C');
        $this->addSql('DROP TABLE filesystem_file');
        $this->addSql('DROP TABLE filesystem_file_permission');
    }
}
