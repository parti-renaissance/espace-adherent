<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181130111908 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ideas_workshop_category (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, canonical_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX category_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ideas_workshop_consultation_report (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, position SMALLINT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ideas_workshop_need (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, canonical_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX need_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ideas_workshop_thread (id INT AUTO_INCREMENT NOT NULL, answer_id INT DEFAULT NULL, status VARCHAR(9) DEFAULT \'SUBMITTED\' NOT NULL, INDEX IDX_CE975BDDAA334807 (answer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ideas_workshop_question (id INT AUTO_INCREMENT NOT NULL, guideline_id INT DEFAULT NULL, placeholder VARCHAR(255) NOT NULL, position SMALLINT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, required TINYINT(1) NOT NULL, published TINYINT(1) NOT NULL, INDEX IDX_111C43E4CC0B46A8 (guideline_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ideas_workshop_idea (id INT UNSIGNED AUTO_INCREMENT NOT NULL, theme_id INT DEFAULT NULL, category_id INT DEFAULT NULL, adherent_id INT UNSIGNED DEFAULT NULL, committee_id INT UNSIGNED DEFAULT NULL, published_at DATETIME DEFAULT NULL, status VARCHAR(11) DEFAULT \'PENDING\' NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, name VARCHAR(255) NOT NULL, canonical_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, INDEX IDX_CA001C7259027487 (theme_id), INDEX IDX_CA001C7212469DE2 (category_id), INDEX IDX_CA001C7225F06C53 (adherent_id), INDEX IDX_CA001C72ED1A100B (committee_id), UNIQUE INDEX idea_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ideas_workshop_ideas_needs (idea_id INT UNSIGNED NOT NULL, need_id INT NOT NULL, INDEX IDX_75CEB995B6FEF7D (idea_id), INDEX IDX_75CEB99624AF264 (need_id), PRIMARY KEY(idea_id, need_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ideas_workshop_consultation (id INT AUTO_INCREMENT NOT NULL, response_time SMALLINT UNSIGNED NOT NULL, started_at DATETIME NOT NULL, ended_at DATETIME NOT NULL, url VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ideas_workshop_guideline (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) NOT NULL, position SMALLINT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ideas_workshop_comment (id INT AUTO_INCREMENT NOT NULL, thread_id INT DEFAULT NULL, adherent_id INT UNSIGNED DEFAULT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_18589988E2904019 (thread_id), INDEX IDX_1858998825F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ideas_workshop_answer (id INT AUTO_INCREMENT NOT NULL, question_id INT DEFAULT NULL, idea_id INT UNSIGNED DEFAULT NULL, content LONGTEXT NOT NULL, INDEX IDX_256A5D7B1E27F6BF (question_id), INDEX IDX_256A5D7B5B6FEF7D (idea_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ideas_workshop_theme (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, canonical_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, image_name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX theme_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ideas_workshop_thread ADD CONSTRAINT FK_CE975BDDAA334807 FOREIGN KEY (answer_id) REFERENCES ideas_workshop_answer (id)');
        $this->addSql('ALTER TABLE ideas_workshop_question ADD CONSTRAINT FK_111C43E4CC0B46A8 FOREIGN KEY (guideline_id) REFERENCES ideas_workshop_guideline (id)');
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD CONSTRAINT FK_CA001C7259027487 FOREIGN KEY (theme_id) REFERENCES ideas_workshop_theme (id)');
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD CONSTRAINT FK_CA001C7212469DE2 FOREIGN KEY (category_id) REFERENCES ideas_workshop_category (id)');
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD CONSTRAINT FK_CA001C7225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD CONSTRAINT FK_CA001C72ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE ideas_workshop_ideas_needs ADD CONSTRAINT FK_75CEB995B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ideas_workshop_ideas_needs ADD CONSTRAINT FK_75CEB99624AF264 FOREIGN KEY (need_id) REFERENCES ideas_workshop_need (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD CONSTRAINT FK_18589988E2904019 FOREIGN KEY (thread_id) REFERENCES ideas_workshop_thread (id)');
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD CONSTRAINT FK_1858998825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE ideas_workshop_answer ADD CONSTRAINT FK_256A5D7B1E27F6BF FOREIGN KEY (question_id) REFERENCES ideas_workshop_question (id)');
        $this->addSql('ALTER TABLE ideas_workshop_answer ADD CONSTRAINT FK_256A5D7B5B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP FOREIGN KEY FK_CA001C7212469DE2');
        $this->addSql('ALTER TABLE ideas_workshop_ideas_needs DROP FOREIGN KEY FK_75CEB99624AF264');
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP FOREIGN KEY FK_18589988E2904019');
        $this->addSql('ALTER TABLE ideas_workshop_answer DROP FOREIGN KEY FK_256A5D7B1E27F6BF');
        $this->addSql('ALTER TABLE ideas_workshop_ideas_needs DROP FOREIGN KEY FK_75CEB995B6FEF7D');
        $this->addSql('ALTER TABLE ideas_workshop_answer DROP FOREIGN KEY FK_256A5D7B5B6FEF7D');
        $this->addSql('ALTER TABLE ideas_workshop_question DROP FOREIGN KEY FK_111C43E4CC0B46A8');
        $this->addSql('ALTER TABLE ideas_workshop_thread DROP FOREIGN KEY FK_CE975BDDAA334807');
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP FOREIGN KEY FK_CA001C7259027487');
        $this->addSql('DROP TABLE ideas_workshop_category');
        $this->addSql('DROP TABLE ideas_workshop_consultation_report');
        $this->addSql('DROP TABLE ideas_workshop_need');
        $this->addSql('DROP TABLE ideas_workshop_thread');
        $this->addSql('DROP TABLE ideas_workshop_question');
        $this->addSql('DROP TABLE ideas_workshop_idea');
        $this->addSql('DROP TABLE ideas_workshop_ideas_needs');
        $this->addSql('DROP TABLE ideas_workshop_consultation');
        $this->addSql('DROP TABLE ideas_workshop_guideline');
        $this->addSql('DROP TABLE ideas_workshop_comment');
        $this->addSql('DROP TABLE ideas_workshop_answer');
        $this->addSql('DROP TABLE ideas_workshop_theme');
    }
}
