<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210601135729 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_answer_user_documents DROP FOREIGN KEY FK_824E75E79C97E9FB');
        $this->addSql('ALTER TABLE ideas_workshop_thread DROP FOREIGN KEY FK_CE975BDDAA334807');
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP FOREIGN KEY FK_CA001C7212469DE2');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA7453A31E89B');
        $this->addSql('ALTER TABLE ideas_workshop_question DROP FOREIGN KEY FK_111C43E4CC0B46A8');
        $this->addSql('ALTER TABLE ideas_workshop_answer DROP FOREIGN KEY FK_256A5D7B5B6FEF7D');
        $this->addSql('ALTER TABLE ideas_workshop_ideas_needs DROP FOREIGN KEY FK_75CEB995B6FEF7D');
        $this->addSql('ALTER TABLE ideas_workshop_ideas_themes DROP FOREIGN KEY FK_DB4ED3145B6FEF7D');
        $this->addSql('ALTER TABLE ideas_workshop_vote DROP FOREIGN KEY FK_9A9B53535B6FEF7D');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA7455B6FEF7D');
        $this->addSql('ALTER TABLE ideas_workshop_ideas_needs DROP FOREIGN KEY FK_75CEB99624AF264');
        $this->addSql('ALTER TABLE ideas_workshop_answer DROP FOREIGN KEY FK_256A5D7B1E27F6BF');
        $this->addSql('ALTER TABLE ideas_workshop_ideas_themes DROP FOREIGN KEY FK_DB4ED31459027487');
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP FOREIGN KEY FK_18589988E2904019');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745E2904019');
        $this->addSql('DROP TABLE ideas_workshop_answer');
        $this->addSql('DROP TABLE ideas_workshop_answer_user_documents');
        $this->addSql('DROP TABLE ideas_workshop_category');
        $this->addSql('DROP TABLE ideas_workshop_comment');
        $this->addSql('DROP TABLE ideas_workshop_consultation');
        $this->addSql('DROP TABLE ideas_workshop_consultation_report');
        $this->addSql('DROP TABLE ideas_workshop_guideline');
        $this->addSql('DROP TABLE ideas_workshop_idea');
        $this->addSql('DROP TABLE ideas_workshop_idea_notification_dates');
        $this->addSql('DROP TABLE ideas_workshop_ideas_needs');
        $this->addSql('DROP TABLE ideas_workshop_ideas_themes');
        $this->addSql('DROP TABLE ideas_workshop_need');
        $this->addSql('DROP TABLE ideas_workshop_question');
        $this->addSql('DROP TABLE ideas_workshop_theme');
        $this->addSql('DROP TABLE ideas_workshop_thread');
        $this->addSql('DROP TABLE ideas_workshop_vote');
        $this->addSql('ALTER TABLE chez_vous_measure_types DROP ideas_workshop_link');
        $this->addSql('DROP INDEX IDX_F11FA7453A31E89B ON reports');
        $this->addSql('DROP INDEX IDX_F11FA7455B6FEF7D ON reports');
        $this->addSql('DROP INDEX IDX_F11FA745E2904019 ON reports');

        $this->addSql('DELETE FROM reports WHERE idea_id IS NOT NULL OR thread_id IS NOT NULL OR thread_comment_id IS NOT NULL');

        $this->addSql('ALTER TABLE reports DROP idea_id, DROP thread_id, DROP thread_comment_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ideas_workshop_answer (
          id INT AUTO_INCREMENT NOT NULL,
          question_id INT NOT NULL,
          idea_id INT UNSIGNED NOT NULL,
          content LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          INDEX IDX_256A5D7B1E27F6BF (question_id),
          INDEX IDX_256A5D7B5B6FEF7D (idea_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_answer_user_documents (
          ideas_workshop_answer_id INT NOT NULL,
          user_document_id INT UNSIGNED NOT NULL,
          INDEX IDX_824E75E76A24B1A2 (user_document_id),
          INDEX IDX_824E75E79C97E9FB (ideas_workshop_answer_id),
          PRIMARY KEY(
            ideas_workshop_answer_id, user_document_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_category (
          id INT AUTO_INCREMENT NOT NULL,
          enabled TINYINT(1) NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          UNIQUE INDEX category_name_unique (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_comment (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          thread_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED NOT NULL,
          content LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          deleted_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          approved TINYINT(1) NOT NULL,
          enabled TINYINT(1) DEFAULT \'1\' NOT NULL,
          UNIQUE INDEX threads_comments_uuid_unique (uuid),
          INDEX IDX_18589988F675F31B (author_id),
          INDEX IDX_18589988E2904019 (thread_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_consultation (
          id INT AUTO_INCREMENT NOT NULL,
          response_time SMALLINT UNSIGNED NOT NULL,
          started_at DATETIME NOT NULL,
          ended_at DATETIME NOT NULL,
          url VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          enabled TINYINT(1) DEFAULT NULL,
          UNIQUE INDEX consultation_enabled_unique (enabled),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_consultation_report (
          id INT AUTO_INCREMENT NOT NULL,
          url VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          position SMALLINT UNSIGNED NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_guideline (
          id INT AUTO_INCREMENT NOT NULL,
          enabled TINYINT(1) NOT NULL,
          position SMALLINT UNSIGNED NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_idea (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          category_id INT DEFAULT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          committee_id INT UNSIGNED DEFAULT NULL,
          published_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          canonical_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          votes_count INT UNSIGNED NOT NULL,
          author_category VARCHAR(9) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          finalized_at DATETIME DEFAULT NULL,
          enabled TINYINT(1) DEFAULT \'1\' NOT NULL,
          comments_count INT UNSIGNED DEFAULT 0 NOT NULL,
          last_contribution_notification_date DATETIME DEFAULT NULL,
          extensions_count SMALLINT UNSIGNED NOT NULL,
          last_extension_date DATE DEFAULT NULL,
          UNIQUE INDEX idea_slug_unique (slug),
          INDEX IDX_CA001C7212469DE2 (category_id),
          INDEX IDX_CA001C72F675F31B (author_id),
          UNIQUE INDEX idea_uuid_unique (uuid),
          INDEX IDX_CA001C72ED1A100B (committee_id),
          INDEX idea_workshop_author_category_idx (author_category),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_idea_notification_dates (
          last_date DATETIME DEFAULT NULL, caution_last_date DATETIME DEFAULT NULL
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_ideas_needs (
          idea_id INT UNSIGNED NOT NULL,
          need_id INT NOT NULL,
          INDEX IDX_75CEB99624AF264 (need_id),
          INDEX IDX_75CEB995B6FEF7D (idea_id),
          PRIMARY KEY(idea_id, need_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_ideas_themes (
          idea_id INT UNSIGNED NOT NULL,
          theme_id INT NOT NULL,
          INDEX IDX_DB4ED31459027487 (theme_id),
          INDEX IDX_DB4ED3145B6FEF7D (idea_id),
          PRIMARY KEY(idea_id, theme_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_need (
          id INT AUTO_INCREMENT NOT NULL,
          enabled TINYINT(1) NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          UNIQUE INDEX need_name_unique (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_question (
          id INT AUTO_INCREMENT NOT NULL,
          guideline_id INT NOT NULL,
          placeholder VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          position SMALLINT UNSIGNED NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          required TINYINT(1) NOT NULL,
          enabled TINYINT(1) NOT NULL,
          category VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          INDEX IDX_111C43E4CC0B46A8 (guideline_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_theme (
          id INT AUTO_INCREMENT NOT NULL,
          enabled TINYINT(1) NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          image_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          position SMALLINT UNSIGNED NOT NULL,
          UNIQUE INDEX theme_name_unique (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_thread (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          answer_id INT NOT NULL,
          author_id INT UNSIGNED NOT NULL,
          content LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          deleted_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          approved TINYINT(1) NOT NULL,
          enabled TINYINT(1) DEFAULT \'1\' NOT NULL,
          UNIQUE INDEX threads_uuid_unique (uuid),
          INDEX IDX_CE975BDDF675F31B (author_id),
          INDEX IDX_CE975BDDAA334807 (answer_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ideas_workshop_vote (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          idea_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED NOT NULL,
          type VARCHAR(10) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          INDEX IDX_9A9B53535B6FEF7D (idea_id),
          INDEX IDX_9A9B5353F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          ideas_workshop_answer
        ADD
          CONSTRAINT FK_256A5D7B1E27F6BF FOREIGN KEY (question_id) REFERENCES ideas_workshop_question (id)');
        $this->addSql('ALTER TABLE
          ideas_workshop_answer
        ADD
          CONSTRAINT FK_256A5D7B5B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ideas_workshop_answer_user_documents
        ADD
          CONSTRAINT FK_824E75E76A24B1A2 FOREIGN KEY (user_document_id) REFERENCES user_documents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ideas_workshop_answer_user_documents
        ADD
          CONSTRAINT FK_824E75E79C97E9FB FOREIGN KEY (ideas_workshop_answer_id) REFERENCES ideas_workshop_answer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ideas_workshop_comment
        ADD
          CONSTRAINT FK_18589988E2904019 FOREIGN KEY (thread_id) REFERENCES ideas_workshop_thread (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ideas_workshop_comment
        ADD
          CONSTRAINT FK_18589988F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ideas_workshop_idea
        ADD
          CONSTRAINT FK_CA001C7212469DE2 FOREIGN KEY (category_id) REFERENCES ideas_workshop_category (id)');
        $this->addSql('ALTER TABLE
          ideas_workshop_idea
        ADD
          CONSTRAINT FK_CA001C72ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE
          ideas_workshop_idea
        ADD
          CONSTRAINT FK_CA001C72F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          ideas_workshop_ideas_needs
        ADD
          CONSTRAINT FK_75CEB995B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ideas_workshop_ideas_needs
        ADD
          CONSTRAINT FK_75CEB99624AF264 FOREIGN KEY (need_id) REFERENCES ideas_workshop_need (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ideas_workshop_ideas_themes
        ADD
          CONSTRAINT FK_DB4ED31459027487 FOREIGN KEY (theme_id) REFERENCES ideas_workshop_theme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ideas_workshop_ideas_themes
        ADD
          CONSTRAINT FK_DB4ED3145B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ideas_workshop_question
        ADD
          CONSTRAINT FK_111C43E4CC0B46A8 FOREIGN KEY (guideline_id) REFERENCES ideas_workshop_guideline (id)');
        $this->addSql('ALTER TABLE
          ideas_workshop_thread
        ADD
          CONSTRAINT FK_CE975BDDAA334807 FOREIGN KEY (answer_id) REFERENCES ideas_workshop_answer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ideas_workshop_thread
        ADD
          CONSTRAINT FK_CE975BDDF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ideas_workshop_vote
        ADD
          CONSTRAINT FK_9A9B53535B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ideas_workshop_vote
        ADD
          CONSTRAINT FK_9A9B5353F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          chez_vous_measure_types
        ADD
          ideas_workshop_link VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE
          reports
        ADD
          idea_id INT UNSIGNED DEFAULT NULL,
        ADD
          thread_id INT UNSIGNED DEFAULT NULL,
        ADD
          thread_comment_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          reports
        ADD
          CONSTRAINT FK_F11FA7453A31E89B FOREIGN KEY (thread_comment_id) REFERENCES ideas_workshop_comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          reports
        ADD
          CONSTRAINT FK_F11FA7455B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          reports
        ADD
          CONSTRAINT FK_F11FA745E2904019 FOREIGN KEY (thread_id) REFERENCES ideas_workshop_thread (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_F11FA7453A31E89B ON reports (thread_comment_id)');
        $this->addSql('CREATE INDEX IDX_F11FA7455B6FEF7D ON reports (idea_id)');
        $this->addSql('CREATE INDEX IDX_F11FA745E2904019 ON reports (thread_id)');
    }
}
