<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181127152341 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE note_consultation_report (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, position SMALLINT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note_need (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, canonical_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX need_name_unique (name), UNIQUE INDEX need_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note_comment (id INT UNSIGNED AUTO_INCREMENT NOT NULL, thread_id INT UNSIGNED DEFAULT NULL, adherent_id INT UNSIGNED DEFAULT NULL, text LONGTEXT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_15D296F6E2904019 (thread_id), INDEX IDX_15D296F625F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note_thread (id INT UNSIGNED AUTO_INCREMENT NOT NULL, answer_id INT DEFAULT NULL, status VARCHAR(9) DEFAULT \'SUBMITTED\' NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_29F21B57AA334807 (answer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note_question (id INT AUTO_INCREMENT NOT NULL, guideline_id INT DEFAULT NULL, placeholder VARCHAR(255) NOT NULL, position SMALLINT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, mandatory TINYINT(1) NOT NULL, published TINYINT(1) NOT NULL, INDEX IDX_A6AC95D0CC0B46A8 (guideline_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note_consultation (id INT AUTO_INCREMENT NOT NULL, response_time SMALLINT UNSIGNED NOT NULL, started_at DATE NOT NULL, ended_at DATE NOT NULL, url VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note_guideline (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) NOT NULL, position SMALLINT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note_scale (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, canonical_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX scale_name_unique (name), UNIQUE INDEX scale_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note_answer (id INT AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED DEFAULT NULL, question_id INT DEFAULT NULL, text LONGTEXT NOT NULL, INDEX IDX_C20F1DF125F06C53 (adherent_id), UNIQUE INDEX UNIQ_C20F1DF11E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note_note (id INT UNSIGNED AUTO_INCREMENT NOT NULL, theme_id INT DEFAULT NULL, scale_id INT DEFAULT NULL, adherent_id INT UNSIGNED DEFAULT NULL, committee_id INT UNSIGNED DEFAULT NULL, published_at DATETIME DEFAULT NULL, status VARCHAR(11) DEFAULT \'IN PROGRESS\' NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, name VARCHAR(255) NOT NULL, canonical_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, INDEX IDX_6FC7EB4C59027487 (theme_id), INDEX IDX_6FC7EB4CF73142C2 (scale_id), INDEX IDX_6FC7EB4C25F06C53 (adherent_id), INDEX IDX_6FC7EB4CED1A100B (committee_id), UNIQUE INDEX note_name_unique (name), UNIQUE INDEX note_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note_notes_needs (note_id INT UNSIGNED NOT NULL, need_id INT NOT NULL, INDEX IDX_D617C1CB26ED0855 (note_id), INDEX IDX_D617C1CB624AF264 (need_id), PRIMARY KEY(note_id, need_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note_notes_guidelines (note_id INT UNSIGNED NOT NULL, guideline_id INT NOT NULL, INDEX IDX_3CB962126ED0855 (note_id), INDEX IDX_3CB9621CC0B46A8 (guideline_id), PRIMARY KEY(note_id, guideline_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note_theme (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, canonical_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, image_name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX theme_name_unique (name), UNIQUE INDEX theme_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE note_comment ADD CONSTRAINT FK_15D296F6E2904019 FOREIGN KEY (thread_id) REFERENCES note_thread (id)');
        $this->addSql('ALTER TABLE note_comment ADD CONSTRAINT FK_15D296F625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE note_thread ADD CONSTRAINT FK_29F21B57AA334807 FOREIGN KEY (answer_id) REFERENCES note_answer (id)');
        $this->addSql('ALTER TABLE note_question ADD CONSTRAINT FK_A6AC95D0CC0B46A8 FOREIGN KEY (guideline_id) REFERENCES note_guideline (id)');
        $this->addSql('ALTER TABLE note_answer ADD CONSTRAINT FK_C20F1DF125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE note_answer ADD CONSTRAINT FK_C20F1DF11E27F6BF FOREIGN KEY (question_id) REFERENCES note_question (id)');
        $this->addSql('ALTER TABLE note_note ADD CONSTRAINT FK_6FC7EB4C59027487 FOREIGN KEY (theme_id) REFERENCES note_theme (id)');
        $this->addSql('ALTER TABLE note_note ADD CONSTRAINT FK_6FC7EB4CF73142C2 FOREIGN KEY (scale_id) REFERENCES note_scale (id)');
        $this->addSql('ALTER TABLE note_note ADD CONSTRAINT FK_6FC7EB4C25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE note_note ADD CONSTRAINT FK_6FC7EB4CED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE note_notes_needs ADD CONSTRAINT FK_D617C1CB26ED0855 FOREIGN KEY (note_id) REFERENCES note_note (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE note_notes_needs ADD CONSTRAINT FK_D617C1CB624AF264 FOREIGN KEY (need_id) REFERENCES note_need (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE note_notes_guidelines ADD CONSTRAINT FK_3CB962126ED0855 FOREIGN KEY (note_id) REFERENCES note_note (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE note_notes_guidelines ADD CONSTRAINT FK_3CB9621CC0B46A8 FOREIGN KEY (guideline_id) REFERENCES note_guideline (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE note_notes_needs DROP FOREIGN KEY FK_D617C1CB624AF264');
        $this->addSql('ALTER TABLE note_comment DROP FOREIGN KEY FK_15D296F6E2904019');
        $this->addSql('ALTER TABLE note_answer DROP FOREIGN KEY FK_C20F1DF11E27F6BF');
        $this->addSql('ALTER TABLE note_question DROP FOREIGN KEY FK_A6AC95D0CC0B46A8');
        $this->addSql('ALTER TABLE note_notes_guidelines DROP FOREIGN KEY FK_3CB9621CC0B46A8');
        $this->addSql('ALTER TABLE note_note DROP FOREIGN KEY FK_6FC7EB4CF73142C2');
        $this->addSql('ALTER TABLE note_thread DROP FOREIGN KEY FK_29F21B57AA334807');
        $this->addSql('ALTER TABLE note_notes_needs DROP FOREIGN KEY FK_D617C1CB26ED0855');
        $this->addSql('ALTER TABLE note_notes_guidelines DROP FOREIGN KEY FK_3CB962126ED0855');
        $this->addSql('ALTER TABLE note_note DROP FOREIGN KEY FK_6FC7EB4C59027487');
        $this->addSql('DROP TABLE note_consultation_report');
        $this->addSql('DROP TABLE note_need');
        $this->addSql('DROP TABLE note_comment');
        $this->addSql('DROP TABLE note_thread');
        $this->addSql('DROP TABLE note_question');
        $this->addSql('DROP TABLE note_consultation');
        $this->addSql('DROP TABLE note_guideline');
        $this->addSql('DROP TABLE note_scale');
        $this->addSql('DROP TABLE note_answer');
        $this->addSql('DROP TABLE note_note');
        $this->addSql('DROP TABLE note_notes_needs');
        $this->addSql('DROP TABLE note_notes_guidelines');
        $this->addSql('DROP TABLE note_theme');
    }
}
