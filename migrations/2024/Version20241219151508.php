<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241219151508 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE je_marche_reports');
        $this->addSql('ALTER TABLE jecoute_news DROP FOREIGN KEY FK_3436209B03A8386');
        $this->addSql('DROP INDEX IDX_3436209B03A8386 ON jecoute_news');
        $this->addSql('ALTER TABLE
          jecoute_news
        ADD
          committee_id INT UNSIGNED DEFAULT NULL,
        ADD
          updated_by_administrator_id INT DEFAULT NULL,
        DROP
          space,
        CHANGE
          created_by_id created_by_administrator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          jecoute_news
        ADD
          CONSTRAINT FK_3436209ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE
          jecoute_news
        ADD
          CONSTRAINT FK_34362099DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_news
        ADD
          CONSTRAINT FK_3436209CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_3436209ED1A100B ON jecoute_news (committee_id)');
        $this->addSql('CREATE INDEX IDX_34362099DF5350C ON jecoute_news (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_3436209CF1918FF ON jecoute_news (updated_by_administrator_id)');
        $this->addSql('ALTER TABLE notification ADD scope VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          push_token
        ADD
          last_active_date DATETIME DEFAULT NULL,
        CHANGE
          source source VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE je_marche_reports (
          id INT AUTO_INCREMENT NOT NULL,
          type VARCHAR(30) CHARACTER
          SET
            utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
            email_address VARCHAR(255) CHARACTER
          SET
            utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
            postal_code VARCHAR(11) CHARACTER
          SET
            utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
            convinced LONGTEXT CHARACTER
          SET
            utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
            almost_convinced LONGTEXT CHARACTER
          SET
            utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
            not_convinced SMALLINT UNSIGNED DEFAULT NULL,
            reaction LONGTEXT CHARACTER
          SET
            utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER
        SET
          utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE jecoute_news DROP FOREIGN KEY FK_3436209ED1A100B');
        $this->addSql('ALTER TABLE jecoute_news DROP FOREIGN KEY FK_34362099DF5350C');
        $this->addSql('ALTER TABLE jecoute_news DROP FOREIGN KEY FK_3436209CF1918FF');
        $this->addSql('DROP INDEX IDX_3436209ED1A100B ON jecoute_news');
        $this->addSql('DROP INDEX IDX_34362099DF5350C ON jecoute_news');
        $this->addSql('DROP INDEX IDX_3436209CF1918FF ON jecoute_news');
        $this->addSql('ALTER TABLE
          jecoute_news
        ADD
          created_by_id INT DEFAULT NULL,
        ADD
          space VARCHAR(255) DEFAULT NULL,
        DROP
          committee_id,
        DROP
          created_by_administrator_id,
        DROP
          updated_by_administrator_id');
        $this->addSql('ALTER TABLE
          jecoute_news
        ADD
          CONSTRAINT FK_3436209B03A8386 FOREIGN KEY (created_by_id) REFERENCES administrators (id) ON
        UPDATE
          NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_3436209B03A8386 ON jecoute_news (created_by_id)');
        $this->addSql('ALTER TABLE notification DROP scope');
        $this->addSql('ALTER TABLE push_token DROP last_active_date, CHANGE source source VARCHAR(255) NOT NULL');
    }
}
