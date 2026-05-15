<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260515162904 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE video (
                  title VARCHAR(255) NOT NULL,
                  status VARCHAR(32) NOT NULL,
                  media_path VARCHAR(255) DEFAULT NULL,
                  duration INT UNSIGNED DEFAULT NULL,
                  width INT UNSIGNED DEFAULT NULL,
                  height INT UNSIGNED DEFAULT NULL,
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  uuid CHAR(36) NOT NULL,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  created_by_administrator_id INT DEFAULT NULL,
                  updated_by_administrator_id INT DEFAULT NULL,
                  UNIQUE INDEX UNIQ_7CC7DA2CD17F50A6 (uuid),
                  INDEX IDX_7CC7DA2C9DF5350C (created_by_administrator_id),
                  INDEX IDX_7CC7DA2CCF1918FF (updated_by_administrator_id),
                  PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  video
                ADD
                  CONSTRAINT FK_7CC7DA2C9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  video
                ADD
                  CONSTRAINT FK_7CC7DA2CCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
                SET
                  NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE video DROP FOREIGN KEY FK_7CC7DA2C9DF5350C');
        $this->addSql('ALTER TABLE video DROP FOREIGN KEY FK_7CC7DA2CCF1918FF');
        $this->addSql('DROP TABLE video');
    }
}
