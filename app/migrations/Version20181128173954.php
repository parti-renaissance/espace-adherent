<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181128173954 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE iw_category (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, canonical_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX category_name_unique (name), UNIQUE INDEX category_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE iw_consultation_report (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, position SMALLINT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE iw_need (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, canonical_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX need_name_unique (name), UNIQUE INDEX need_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE iw_comment (id INT AUTO_INCREMENT NOT NULL, thread_id INT DEFAULT NULL, adherent_id INT UNSIGNED DEFAULT NULL, text LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_DFA485A8E2904019 (thread_id), INDEX IDX_DFA485A825F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE iw_thread (id INT AUTO_INCREMENT NOT NULL, answer_id INT DEFAULT NULL, status VARCHAR(9) DEFAULT \'SUBMITTED\' NOT NULL, INDEX IDX_E523CFA6AA334807 (answer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE iw_question (id INT AUTO_INCREMENT NOT NULL, guideline_id INT DEFAULT NULL, placeholder VARCHAR(255) NOT NULL, position SMALLINT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, required TINYINT(1) NOT NULL, published TINYINT(1) NOT NULL, INDEX IDX_2AB59F30CC0B46A8 (guideline_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE iw_idea (id INT UNSIGNED AUTO_INCREMENT NOT NULL, theme_id INT DEFAULT NULL, category_id INT DEFAULT NULL, adherent_id INT UNSIGNED DEFAULT NULL, committee_id INT UNSIGNED DEFAULT NULL, published_at DATETIME DEFAULT NULL, status VARCHAR(11) DEFAULT \'IN PROGRESS\' NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, name VARCHAR(255) NOT NULL, canonical_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, INDEX IDX_4E9A67259027487 (theme_id), INDEX IDX_4E9A67212469DE2 (category_id), INDEX IDX_4E9A67225F06C53 (adherent_id), INDEX IDX_4E9A672ED1A100B (committee_id), UNIQUE INDEX idea_name_unique (name), UNIQUE INDEX idea_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE iw_ideas_needs (idea_id INT UNSIGNED NOT NULL, need_id INT NOT NULL, INDEX IDX_F5BE7115B6FEF7D (idea_id), INDEX IDX_F5BE711624AF264 (need_id), PRIMARY KEY(idea_id, need_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE iw_consultation (id INT AUTO_INCREMENT NOT NULL, response_time SMALLINT UNSIGNED NOT NULL, started_at DATE NOT NULL, ended_at DATE NOT NULL, url VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE iw_guideline (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) NOT NULL, position SMALLINT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE iw_answer (id INT AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED DEFAULT NULL, question_id INT DEFAULT NULL, idea_id INT UNSIGNED DEFAULT NULL, text LONGTEXT NOT NULL, INDEX IDX_EDEC90025F06C53 (adherent_id), INDEX IDX_EDEC9001E27F6BF (question_id), INDEX IDX_EDEC9005B6FEF7D (idea_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE iw_theme (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, canonical_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, image_name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX theme_name_unique (name), UNIQUE INDEX theme_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE iw_comment ADD CONSTRAINT FK_DFA485A8E2904019 FOREIGN KEY (thread_id) REFERENCES iw_thread (id)');
        $this->addSql('ALTER TABLE iw_comment ADD CONSTRAINT FK_DFA485A825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE iw_thread ADD CONSTRAINT FK_E523CFA6AA334807 FOREIGN KEY (answer_id) REFERENCES iw_answer (id)');
        $this->addSql('ALTER TABLE iw_question ADD CONSTRAINT FK_2AB59F30CC0B46A8 FOREIGN KEY (guideline_id) REFERENCES iw_guideline (id)');
        $this->addSql('ALTER TABLE iw_idea ADD CONSTRAINT FK_4E9A67259027487 FOREIGN KEY (theme_id) REFERENCES iw_theme (id)');
        $this->addSql('ALTER TABLE iw_idea ADD CONSTRAINT FK_4E9A67212469DE2 FOREIGN KEY (category_id) REFERENCES iw_category (id)');
        $this->addSql('ALTER TABLE iw_idea ADD CONSTRAINT FK_4E9A67225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE iw_idea ADD CONSTRAINT FK_4E9A672ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE iw_ideas_needs ADD CONSTRAINT FK_F5BE7115B6FEF7D FOREIGN KEY (idea_id) REFERENCES iw_idea (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE iw_ideas_needs ADD CONSTRAINT FK_F5BE711624AF264 FOREIGN KEY (need_id) REFERENCES iw_need (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE iw_answer ADD CONSTRAINT FK_EDEC90025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE iw_answer ADD CONSTRAINT FK_EDEC9001E27F6BF FOREIGN KEY (question_id) REFERENCES iw_question (id)');
        $this->addSql('ALTER TABLE iw_answer ADD CONSTRAINT FK_EDEC9005B6FEF7D FOREIGN KEY (idea_id) REFERENCES iw_idea (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE iw_idea DROP FOREIGN KEY FK_4E9A67212469DE2');
        $this->addSql('ALTER TABLE iw_ideas_needs DROP FOREIGN KEY FK_F5BE711624AF264');
        $this->addSql('ALTER TABLE iw_comment DROP FOREIGN KEY FK_DFA485A8E2904019');
        $this->addSql('ALTER TABLE iw_answer DROP FOREIGN KEY FK_EDEC9001E27F6BF');
        $this->addSql('ALTER TABLE iw_ideas_needs DROP FOREIGN KEY FK_F5BE7115B6FEF7D');
        $this->addSql('ALTER TABLE iw_answer DROP FOREIGN KEY FK_EDEC9005B6FEF7D');
        $this->addSql('ALTER TABLE iw_question DROP FOREIGN KEY FK_2AB59F30CC0B46A8');
        $this->addSql('ALTER TABLE iw_thread DROP FOREIGN KEY FK_E523CFA6AA334807');
        $this->addSql('ALTER TABLE iw_idea DROP FOREIGN KEY FK_4E9A67259027487');
        $this->addSql('DROP TABLE iw_category');
        $this->addSql('DROP TABLE iw_consultation_report');
        $this->addSql('DROP TABLE iw_need');
        $this->addSql('DROP TABLE iw_comment');
        $this->addSql('DROP TABLE iw_thread');
        $this->addSql('DROP TABLE iw_question');
        $this->addSql('DROP TABLE iw_idea');
        $this->addSql('DROP TABLE iw_ideas_needs');
        $this->addSql('DROP TABLE iw_consultation');
        $this->addSql('DROP TABLE iw_guideline');
        $this->addSql('DROP TABLE iw_answer');
        $this->addSql('DROP TABLE iw_theme');
    }
}
