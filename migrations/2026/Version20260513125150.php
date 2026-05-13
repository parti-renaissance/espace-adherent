<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260513125150 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE hub_item DROP FOREIGN KEY FK_195C97A29DF5350C');
        $this->addSql('ALTER TABLE hub_item DROP FOREIGN KEY FK_195C97A2CF1918FF');
        $this->addSql('DROP TABLE hub_item');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE hub_item (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  created_by_administrator_id INT DEFAULT NULL,
                  updated_by_administrator_id INT DEFAULT NULL,
                  title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT '(DC2Type:uuid)',
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  position SMALLINT DEFAULT 0 NOT NULL,
                  INDEX IDX_195C97A29DF5350C (created_by_administrator_id),
                  INDEX IDX_195C97A2CF1918FF (updated_by_administrator_id),
                  UNIQUE INDEX UNIQ_195C97A2D17F50A6 (uuid),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  hub_item
                ADD
                  CONSTRAINT FK_195C97A29DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON
                UPDATE
                  NO ACTION ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  hub_item
                ADD
                  CONSTRAINT FK_195C97A2CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON
                UPDATE
                  NO ACTION ON DELETE
                SET
                  NULL
            SQL);
    }
}
