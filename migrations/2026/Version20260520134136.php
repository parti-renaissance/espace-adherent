<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260520134136 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE email_sender (
                  name VARCHAR(255) NOT NULL,
                  email VARCHAR(255) NOT NULL,
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  uuid CHAR(36) NOT NULL,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  created_by_administrator_id INT DEFAULT NULL,
                  updated_by_administrator_id INT DEFAULT NULL,
                  UNIQUE INDEX UNIQ_229A993FD17F50A6 (uuid),
                  INDEX IDX_229A993F9DF5350C (created_by_administrator_id),
                  INDEX IDX_229A993FCF1918FF (updated_by_administrator_id),
                  PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  email_sender
                ADD
                  CONSTRAINT FK_229A993F9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  email_sender
                ADD
                  CONSTRAINT FK_229A993FCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('ALTER TABLE transactional_email_template ADD sender_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  transactional_email_template
                ADD
                  CONSTRAINT FK_65A0950AF624B39D FOREIGN KEY (sender_id) REFERENCES email_sender (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('CREATE INDEX IDX_65A0950AF624B39D ON transactional_email_template (sender_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE email_sender DROP FOREIGN KEY FK_229A993F9DF5350C');
        $this->addSql('ALTER TABLE email_sender DROP FOREIGN KEY FK_229A993FCF1918FF');
        $this->addSql('DROP TABLE email_sender');
        $this->addSql('ALTER TABLE transactional_email_template DROP FOREIGN KEY FK_65A0950AF624B39D');
        $this->addSql('DROP INDEX IDX_65A0950AF624B39D ON transactional_email_template');
        $this->addSql('ALTER TABLE transactional_email_template DROP sender_id');
    }
}
