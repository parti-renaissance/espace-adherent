<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250730144505 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE app_alert (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  created_by_administrator_id INT DEFAULT NULL,
                  updated_by_administrator_id INT DEFAULT NULL,
                  type VARCHAR(255) NOT NULL,
                  label VARCHAR(255) NOT NULL,
                  title VARCHAR(255) NOT NULL,
                  description VARCHAR(255) NOT NULL,
                  cta_label VARCHAR(255) DEFAULT NULL,
                  cta_url VARCHAR(255) DEFAULT NULL,
                  image_url VARCHAR(255) DEFAULT NULL,
                  share_url VARCHAR(255) DEFAULT NULL,
                  data JSON DEFAULT NULL,
                  begin_at DATETIME NOT NULL,
                  end_at DATETIME NOT NULL,
                  with_magic_link TINYINT(1) DEFAULT 0 NOT NULL,
                  is_active TINYINT(1) DEFAULT 1 NOT NULL,
                  uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  UNIQUE INDEX UNIQ_C12ECB0CD17F50A6 (uuid),
                  INDEX IDX_C12ECB0C9DF5350C (created_by_administrator_id),
                  INDEX IDX_C12ECB0CCF1918FF (updated_by_administrator_id),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_alert
                ADD
                  CONSTRAINT FK_C12ECB0C9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_alert
                ADD
                  CONSTRAINT FK_C12ECB0CCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
                SET
                  NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE app_alert DROP FOREIGN KEY FK_C12ECB0C9DF5350C
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE app_alert DROP FOREIGN KEY FK_C12ECB0CCF1918FF
            SQL);
        $this->addSql(<<<'SQL'
                DROP TABLE app_alert
            SQL);
    }
}
