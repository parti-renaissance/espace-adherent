<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260526071506 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE adherent_signup_source (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  source VARCHAR(100) NOT NULL,
                  captured_at DATETIME NOT NULL,
                  adherent_id INT UNSIGNED NOT NULL,
                  INDEX IDX_EDE7677125F06C53 (adherent_id),
                  UNIQUE INDEX UNIQ_EDE7677125F06C535F8A7F73 (adherent_id, source),
                  PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE signup_source (
                  code VARCHAR(100) NOT NULL,
                  label VARCHAR(255) NOT NULL,
                  enabled TINYINT DEFAULT 1 NOT NULL,
                  friendly_captcha_site_key VARCHAR(255) DEFAULT NULL,
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  uuid CHAR(36) NOT NULL,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  UNIQUE INDEX UNIQ_26C1E7AD77153098 (code),
                  UNIQUE INDEX UNIQ_26C1E7ADD17F50A6 (uuid),
                  PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_signup_source
                ADD
                  CONSTRAINT FK_EDE7677125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_signup_source DROP FOREIGN KEY FK_EDE7677125F06C53');
        $this->addSql('DROP TABLE adherent_signup_source');
        $this->addSql('DROP TABLE signup_source');
    }
}
