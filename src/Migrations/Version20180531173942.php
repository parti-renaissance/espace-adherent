<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180531173942 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mooc_attachment_link (id INT UNSIGNED AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mooc_chapter (id INT UNSIGNED AUTO_INCREMENT NOT NULL, mooc_id INT UNSIGNED DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, published TINYINT(1) NOT NULL, published_at DATETIME NOT NULL, display_order SMALLINT DEFAULT 1 NOT NULL, INDEX IDX_A3EDA0D1255EEB87 (mooc_id), UNIQUE INDEX mooc_chapter_slug (slug), UNIQUE INDEX mooc_chapter_order_display_by_mooc (display_order, mooc_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mooc_elements (id INT UNSIGNED AUTO_INCREMENT NOT NULL, chapter_id INT UNSIGNED DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, display_order SMALLINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, type VARCHAR(255) NOT NULL, youtube_id VARCHAR(255) DEFAULT NULL, content VARCHAR(800) DEFAULT NULL, INDEX IDX_691284C5579F4768 (chapter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mooc_video_attachment_link (video_id INT UNSIGNED NOT NULL, attachment_link_id INT UNSIGNED NOT NULL, INDEX IDX_2A8851529C1004E (video_id), UNIQUE INDEX UNIQ_2A88515653157F7 (attachment_link_id), PRIMARY KEY(video_id, attachment_link_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mooc (id INT UNSIGNED AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, slug VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX mooc_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mooc_chapter ADD CONSTRAINT FK_A3EDA0D1255EEB87 FOREIGN KEY (mooc_id) REFERENCES mooc (id)');
        $this->addSql('ALTER TABLE mooc_elements ADD CONSTRAINT FK_691284C5579F4768 FOREIGN KEY (chapter_id) REFERENCES mooc_chapter (id)');
        $this->addSql('ALTER TABLE mooc_video_attachment_link ADD CONSTRAINT FK_2A8851529C1004E FOREIGN KEY (video_id) REFERENCES mooc_elements (id)');
        $this->addSql('ALTER TABLE mooc_video_attachment_link ADD CONSTRAINT FK_2A88515653157F7 FOREIGN KEY (attachment_link_id) REFERENCES mooc_attachment_link (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_video_attachment_link DROP FOREIGN KEY FK_2A88515653157F7');
        $this->addSql('ALTER TABLE mooc_elements DROP FOREIGN KEY FK_691284C5579F4768');
        $this->addSql('ALTER TABLE mooc_video_attachment_link DROP FOREIGN KEY FK_2A8851529C1004E');
        $this->addSql('ALTER TABLE mooc_chapter DROP FOREIGN KEY FK_A3EDA0D1255EEB87');
        $this->addSql('DROP TABLE mooc_attachment_link');
        $this->addSql('DROP TABLE mooc_chapter');
        $this->addSql('DROP TABLE mooc_elements');
        $this->addSql('DROP TABLE mooc_video_attachment_link');
        $this->addSql('DROP TABLE mooc');
    }
}
