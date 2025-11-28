<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240711214827 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article_proposal_theme DROP FOREIGN KEY FK_F6B9A2217294869C');
        $this->addSql('ALTER TABLE article_proposal_theme DROP FOREIGN KEY FK_F6B9A221B85948AF');
        $this->addSql('ALTER TABLE articles DROP FOREIGN KEY FK_BFDD316812469DE2');
        $this->addSql('ALTER TABLE articles DROP FOREIGN KEY FK_BFDD3168EA9FDD75');
        $this->addSql('ALTER TABLE order_articles DROP FOREIGN KEY FK_5E25D3D9EA9FDD75');
        $this->addSql('ALTER TABLE order_section_order_article DROP FOREIGN KEY FK_A956D4E46BF91E2F');
        $this->addSql('ALTER TABLE order_section_order_article DROP FOREIGN KEY FK_A956D4E4C14E7BC9');
        $this->addSql('DROP TABLE article_proposal_theme');
        $this->addSql('DROP TABLE articles');
        $this->addSql('DROP TABLE articles_categories');
        $this->addSql('DROP TABLE order_articles');
        $this->addSql('DROP TABLE order_section_order_article');
        $this->addSql('DROP TABLE order_sections');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE article_proposal_theme (
          article_id BIGINT NOT NULL,
          proposal_theme_id INT NOT NULL,
          INDEX IDX_F6B9A2217294869C (article_id),
          INDEX IDX_F6B9A221B85948AF (proposal_theme_id),
          PRIMARY KEY(article_id, proposal_theme_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE articles (
          id BIGINT AUTO_INCREMENT NOT NULL,
          category_id INT DEFAULT NULL,
          media_id BIGINT DEFAULT NULL,
          published_at DATETIME NOT NULL,
          published TINYINT(1) NOT NULL,
          display_media TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          deleted_at DATETIME DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          keywords VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          for_renaissance TINYINT(1) DEFAULT 0 NOT NULL,
          json_content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_BFDD316812469DE2 (category_id),
          INDEX IDX_BFDD3168EA9FDD75 (media_id),
          UNIQUE INDEX UNIQ_BFDD3168989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE articles_categories (
          id INT AUTO_INCREMENT NOT NULL,
          position SMALLINT NOT NULL,
          name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          cta_link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          cta_label VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          display TINYINT(1) DEFAULT 1 NOT NULL,
          UNIQUE INDEX UNIQ_DE004A0E989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE order_articles (
          id INT AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          position SMALLINT NOT NULL,
          published TINYINT(1) NOT NULL,
          display_media TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          deleted_at DATETIME DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          keywords VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_5E25D3D9EA9FDD75 (media_id),
          UNIQUE INDEX UNIQ_5E25D3D9989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE order_section_order_article (
          order_article_id INT NOT NULL,
          order_section_id INT NOT NULL,
          INDEX IDX_A956D4E46BF91E2F (order_section_id),
          INDEX IDX_A956D4E4C14E7BC9 (order_article_id),
          PRIMARY KEY(
            order_article_id, order_section_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE order_sections (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          position SMALLINT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          article_proposal_theme
        ADD
          CONSTRAINT FK_F6B9A2217294869C FOREIGN KEY (article_id) REFERENCES articles (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          article_proposal_theme
        ADD
          CONSTRAINT FK_F6B9A221B85948AF FOREIGN KEY (proposal_theme_id) REFERENCES proposals_themes (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          articles
        ADD
          CONSTRAINT FK_BFDD316812469DE2 FOREIGN KEY (category_id) REFERENCES articles_categories (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          articles
        ADD
          CONSTRAINT FK_BFDD3168EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          order_articles
        ADD
          CONSTRAINT FK_5E25D3D9EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          order_section_order_article
        ADD
          CONSTRAINT FK_A956D4E46BF91E2F FOREIGN KEY (order_section_id) REFERENCES order_sections (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          order_section_order_article
        ADD
          CONSTRAINT FK_A956D4E4C14E7BC9 FOREIGN KEY (order_article_id) REFERENCES order_articles (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
