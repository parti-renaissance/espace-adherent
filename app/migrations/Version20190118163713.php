<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190118163713 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_survey DROP FOREIGN KEY FK_EC4948E561220EA6');
        $this->addSql('DROP INDEX IDX_EC4948E561220EA6 ON jecoute_survey');
        $this->addSql('ALTER TABLE jecoute_survey CHANGE creator_id author_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_survey ADD CONSTRAINT FK_EC4948E5F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_EC4948E5F675F31B ON jecoute_survey (author_id)');
        $this->addSql('ALTER TABLE jecoute_choice DROP FOREIGN KEY FK_80BD898B1E27F6BF');
        $this->addSql('ALTER TABLE jecoute_choice ADD CONSTRAINT FK_80BD898B1E27F6BF FOREIGN KEY (question_id) REFERENCES jecoute_question (id)');
        $this->addSql('ALTER TABLE jecoute_data_answer DROP FOREIGN KEY FK_12FB393EA6DF29BA');
        $this->addSql('ALTER TABLE jecoute_data_answer ADD CONSTRAINT FK_12FB393EA6DF29BA FOREIGN KEY (survey_question_id) REFERENCES jecoute_survey_question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices DROP FOREIGN KEY FK_10DF117259C0831');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices DROP FOREIGN KEY FK_10DF117998666D1');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices ADD CONSTRAINT FK_10DF117259C0831 FOREIGN KEY (data_answer_id) REFERENCES jecoute_data_answer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices ADD CONSTRAINT FK_10DF117998666D1 FOREIGN KEY (choice_id) REFERENCES jecoute_choice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jecoute_survey_question DROP FOREIGN KEY FK_A2FBFA811E27F6BF');
        $this->addSql('ALTER TABLE jecoute_survey_question DROP FOREIGN KEY FK_A2FBFA81B3FE509D');
        $this->addSql('ALTER TABLE jecoute_survey_question ADD uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE jecoute_survey_question ADD CONSTRAINT FK_A2FBFA811E27F6BF FOREIGN KEY (question_id) REFERENCES jecoute_question (id)');
        $this->addSql('ALTER TABLE jecoute_survey_question ADD CONSTRAINT FK_A2FBFA81B3FE509D FOREIGN KEY (survey_id) REFERENCES jecoute_survey (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_choice DROP FOREIGN KEY FK_80BD898B1E27F6BF');
        $this->addSql('ALTER TABLE jecoute_choice ADD CONSTRAINT FK_80BD898B1E27F6BF FOREIGN KEY (question_id) REFERENCES jecoute_question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jecoute_data_answer DROP FOREIGN KEY FK_12FB393EA6DF29BA');
        $this->addSql('ALTER TABLE jecoute_data_answer ADD CONSTRAINT FK_12FB393EA6DF29BA FOREIGN KEY (survey_question_id) REFERENCES jecoute_survey_question (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices DROP FOREIGN KEY FK_10DF117259C0831');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices DROP FOREIGN KEY FK_10DF117998666D1');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices ADD CONSTRAINT FK_10DF117259C0831 FOREIGN KEY (data_answer_id) REFERENCES jecoute_data_answer (id)');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices ADD CONSTRAINT FK_10DF117998666D1 FOREIGN KEY (choice_id) REFERENCES jecoute_choice (id)');
        $this->addSql('ALTER TABLE jecoute_survey DROP FOREIGN KEY FK_EC4948E5F675F31B');
        $this->addSql('DROP INDEX IDX_EC4948E5F675F31B ON jecoute_survey');
        $this->addSql('ALTER TABLE jecoute_survey CHANGE author_id creator_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_survey ADD CONSTRAINT FK_EC4948E561220EA6 FOREIGN KEY (creator_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_EC4948E561220EA6 ON jecoute_survey (creator_id)');
        $this->addSql('ALTER TABLE jecoute_survey_question DROP FOREIGN KEY FK_A2FBFA81B3FE509D');
        $this->addSql('ALTER TABLE jecoute_survey_question DROP FOREIGN KEY FK_A2FBFA811E27F6BF');
        $this->addSql('ALTER TABLE jecoute_survey_question DROP uuid');
        $this->addSql('ALTER TABLE jecoute_survey_question ADD CONSTRAINT FK_A2FBFA81B3FE509D FOREIGN KEY (survey_id) REFERENCES jecoute_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jecoute_survey_question ADD CONSTRAINT FK_A2FBFA811E27F6BF FOREIGN KEY (question_id) REFERENCES jecoute_question (id) ON DELETE CASCADE');
    }
}
