<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260326083041 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE tally_form (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  title VARCHAR(255) NOT NULL,
                  slug VARCHAR(255) NOT NULL,
                  tally_id VARCHAR(50) NOT NULL,
                  published TINYINT(1) DEFAULT 1 NOT NULL,
                  uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  utm_source VARCHAR(255) DEFAULT NULL,
                  utm_campaign VARCHAR(255) DEFAULT NULL,
                  UNIQUE INDEX UNIQ_79C06D56989D9B62 (slug),
                  UNIQUE INDEX UNIQ_79C06D56D17F50A6 (uuid),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tally_form');
    }
}
