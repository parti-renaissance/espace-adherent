<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260206155919 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE besoindeurope_inscription_requests');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE besoindeurope_inscription_requests (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  client_ip VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
                  uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT '(DC2Type:uuid)',
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  utm_source VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
                  utm_campaign VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
                  UNIQUE INDEX UNIQ_96473AF5D17F50A6 (uuid),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
    }
}
