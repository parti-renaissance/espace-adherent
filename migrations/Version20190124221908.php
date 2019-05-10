<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190124221908 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE formation_articles (id BIGINT AUTO_INCREMENT NOT NULL, axe_id BIGINT DEFAULT NULL, media_id BIGINT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, content LONGTEXT NOT NULL, display_media TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_784F66992B36786B (title), UNIQUE INDEX UNIQ_784F6699989D9B62 (slug), INDEX IDX_784F66992E30CD41 (axe_id), INDEX IDX_784F6699EA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formation_axes (id BIGINT AUTO_INCREMENT NOT NULL, media_id BIGINT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, content LONGTEXT NOT NULL, display_media TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_7E652CB6989D9B62 (slug), INDEX IDX_7E652CB6EA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE formation_articles ADD CONSTRAINT FK_784F66992E30CD41 FOREIGN KEY (axe_id) REFERENCES formation_axes (id)');
        $this->addSql('ALTER TABLE formation_articles ADD CONSTRAINT FK_784F6699EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE formation_axes ADD CONSTRAINT FK_7E652CB6EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE formation_articles DROP FOREIGN KEY FK_784F66992E30CD41');
        $this->addSql('DROP TABLE formation_articles');
        $this->addSql('DROP TABLE formation_axes');
    }
}
