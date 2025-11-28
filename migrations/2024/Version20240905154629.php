<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240905154629 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE transactional_email_template (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          parent_id INT UNSIGNED DEFAULT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          identifier VARCHAR(255) NOT NULL,
          subject VARCHAR(255) DEFAULT NULL,
          content LONGTEXT DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          json_content LONGTEXT DEFAULT NULL,
          UNIQUE INDEX UNIQ_65A0950AD17F50A6 (uuid),
          INDEX IDX_65A0950A727ACA70 (parent_id),
          INDEX IDX_65A0950A9DF5350C (created_by_administrator_id),
          INDEX IDX_65A0950ACF1918FF (updated_by_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          transactional_email_template
        ADD
          CONSTRAINT FK_65A0950A727ACA70 FOREIGN KEY (parent_id) REFERENCES transactional_email_template (id)');
        $this->addSql('ALTER TABLE
          transactional_email_template
        ADD
          CONSTRAINT FK_65A0950A9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          transactional_email_template
        ADD
          CONSTRAINT FK_65A0950ACF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE emails ADD use_template_endpoint TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transactional_email_template DROP FOREIGN KEY FK_65A0950A727ACA70');
        $this->addSql('ALTER TABLE transactional_email_template DROP FOREIGN KEY FK_65A0950A9DF5350C');
        $this->addSql('ALTER TABLE transactional_email_template DROP FOREIGN KEY FK_65A0950ACF1918FF');
        $this->addSql('DROP TABLE transactional_email_template');
        $this->addSql('ALTER TABLE emails DROP use_template_endpoint');
    }
}
