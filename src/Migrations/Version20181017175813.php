<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181017175813 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE jecoute_survey (
              id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
              creator_id INT UNSIGNED DEFAULT NULL, 
              name VARCHAR(255) NOT NULL, 
              published TINYINT(1) NOT NULL, 
              uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
              created_at DATETIME NOT NULL, 
              updated_at DATETIME NOT NULL, 
              INDEX IDX_EC4948E561220EA6 (creator_id), 
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE jecoute_choice (
              id INT AUTO_INCREMENT NOT NULL, 
              question_id INT DEFAULT NULL, 
              content VARCHAR(255) NOT NULL, 
              position SMALLINT NOT NULL, 
              INDEX IDX_80BD898B1E27F6BF (question_id), 
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE jecoute_survey_question (
              id INT AUTO_INCREMENT NOT NULL, 
              survey_id INT UNSIGNED DEFAULT NULL, 
              question_id INT DEFAULT NULL, 
              position SMALLINT NOT NULL, 
              INDEX IDX_A2FBFA81B3FE509D (survey_id), 
              INDEX IDX_A2FBFA811E27F6BF (question_id), 
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE jecoute_question (
              id INT AUTO_INCREMENT NOT NULL, 
              content VARCHAR(255) NOT NULL, 
              type VARCHAR(255) NOT NULL, 
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB'
        );

        $this->addSql('ALTER TABLE jecoute_survey ADD CONSTRAINT FK_EC4948E561220EA6 FOREIGN KEY (creator_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE jecoute_choice ADD CONSTRAINT FK_80BD898B1E27F6BF FOREIGN KEY (question_id) REFERENCES jecoute_question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jecoute_survey_question ADD CONSTRAINT FK_A2FBFA81B3FE509D FOREIGN KEY (survey_id) REFERENCES jecoute_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jecoute_survey_question ADD CONSTRAINT FK_A2FBFA811E27F6BF FOREIGN KEY (question_id) REFERENCES jecoute_question (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_survey_question DROP FOREIGN KEY FK_A2FBFA81B3FE509D');
        $this->addSql('ALTER TABLE jecoute_choice DROP FOREIGN KEY FK_80BD898B1E27F6BF');
        $this->addSql('ALTER TABLE jecoute_survey_question DROP FOREIGN KEY FK_A2FBFA811E27F6BF');
        $this->addSql('DROP TABLE jecoute_survey');
        $this->addSql('DROP TABLE jecoute_choice');
        $this->addSql('DROP TABLE jecoute_survey_question');
        $this->addSql('DROP TABLE jecoute_question');
    }
}
