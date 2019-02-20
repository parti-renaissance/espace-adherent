<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170324172552 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE social_shares (id BIGINT AUTO_INCREMENT NOT NULL, social_share_category_id BIGINT DEFAULT NULL, media_id BIGINT DEFAULT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(255) NOT NULL, type VARCHAR(10) NOT NULL, position INT NOT NULL, description LONGTEXT NOT NULL, default_url VARCHAR(255) NOT NULL, facebook_url VARCHAR(255) DEFAULT NULL, twitter_url VARCHAR(255) DEFAULT NULL, published TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_8E1413A085040FAD (social_share_category_id), INDEX IDX_8E1413A0EA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE social_share_categories (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(255) NOT NULL, position INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE social_shares ADD CONSTRAINT FK_8E1413A085040FAD FOREIGN KEY (social_share_category_id) REFERENCES social_share_categories (id)');
        $this->addSql('ALTER TABLE social_shares ADD CONSTRAINT FK_8E1413A0EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE social_shares DROP FOREIGN KEY FK_8E1413A085040FAD');
        $this->addSql('DROP TABLE social_shares');
        $this->addSql('DROP TABLE social_share_categories');
    }
}
