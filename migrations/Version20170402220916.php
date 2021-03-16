<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170402220916 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE clarifications (id BIGINT AUTO_INCREMENT NOT NULL, media_id BIGINT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, title VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, description VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, display_media TINYINT(1) NOT NULL, published TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_2FAB8972989D9B62 (slug), INDEX IDX_2FAB8972EA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE clarifications ADD CONSTRAINT FK_2FAB8972EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE clarifications');
    }
}
