<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260413133402 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE renaissance_newsletter_source (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  code VARCHAR(100) NOT NULL,
                  label VARCHAR(255) NOT NULL,
                  confirmation_redirect_url VARCHAR(500) DEFAULT NULL,
                  mailchimp_tag VARCHAR(255) DEFAULT NULL,
                  enabled TINYINT(1) DEFAULT 1 NOT NULL,
                  uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  UNIQUE INDEX UNIQ_87DDA8D77153098 (code),
                  UNIQUE INDEX UNIQ_87DDA8DD17F50A6 (uuid),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE renaissance_newsletter_source');
    }
}
