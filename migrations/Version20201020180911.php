<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201020180911 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE territorial_council_official_report (id INT UNSIGNED AUTO_INCREMENT NOT NULL, political_committee_id INT UNSIGNED NOT NULL, author_id INT UNSIGNED DEFAULT NULL, created_by_id INT UNSIGNED DEFAULT NULL, updated_by_id INT UNSIGNED DEFAULT NULL, name VARCHAR(50) NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_8D80D385C7A72 (political_committee_id), INDEX IDX_8D80D385F675F31B (author_id), INDEX IDX_8D80D385B03A8386 (created_by_id), INDEX IDX_8D80D385896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE territorial_council_official_report_document (id INT UNSIGNED AUTO_INCREMENT NOT NULL, created_by_id INT UNSIGNED DEFAULT NULL, report_id INT UNSIGNED DEFAULT NULL, filename VARCHAR(36) NOT NULL, extension VARCHAR(10) NOT NULL, mime_type VARCHAR(30) NOT NULL, version SMALLINT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_78C1161DB03A8386 (created_by_id), INDEX IDX_78C1161D4BD2A4C0 (report_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE territorial_council_official_report ADD CONSTRAINT FK_8D80D385C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE territorial_council_official_report ADD CONSTRAINT FK_8D80D385F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE territorial_council_official_report ADD CONSTRAINT FK_8D80D385B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE territorial_council_official_report ADD CONSTRAINT FK_8D80D385896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE territorial_council_official_report_document ADD CONSTRAINT FK_78C1161DB03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE territorial_council_official_report_document ADD CONSTRAINT FK_78C1161D4BD2A4C0 FOREIGN KEY (report_id) REFERENCES territorial_council_official_report (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_official_report_document DROP FOREIGN KEY FK_78C1161D4BD2A4C0');
        $this->addSql('DROP TABLE territorial_council_official_report');
        $this->addSql('DROP TABLE territorial_council_official_report_document');
    }
}
