<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240724074801 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE clarifications DROP FOREIGN KEY FK_2FAB8972EA9FDD75');
        $this->addSql('DROP TABLE clarifications');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE clarifications (
          id BIGINT AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          deleted_at DATETIME DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          display_media TINYINT(1) NOT NULL,
          published TINYINT(1) NOT NULL,
          keywords VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_2FAB8972989D9B62 (slug),
          INDEX IDX_2FAB8972EA9FDD75 (media_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          clarifications
        ADD
          CONSTRAINT FK_2FAB8972EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
