<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181022001620 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE jecoute_data_survey (
                id INT AUTO_INCREMENT NOT NULL, 
                author_id INT UNSIGNED DEFAULT NULL, 
                survey_id INT UNSIGNED NOT NULL, 
                posted_at DATETIME NOT NULL, 
                first_name VARCHAR(50) DEFAULT NULL, 
                last_name VARCHAR(50) DEFAULT NULL, 
                phone VARCHAR(255) DEFAULT NULL, 
                email_address VARCHAR(255) DEFAULT NULL, 
                agreed_to_stay_in_contact TINYINT(1) NOT NULL, 
                agreed_to_join_paris_operation TINYINT(1) NOT NULL, 
                INDEX IDX_6579E8E7F675F31B (author_id), 
                INDEX IDX_6579E8E7B3FE509D (survey_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE jecoute_data_answer (
                id INT AUTO_INCREMENT NOT NULL, 
                survey_question_id INT DEFAULT NULL, 
                data_survey_id INT DEFAULT NULL, 
                text_field VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_12FB393EA6DF29BA (survey_question_id), 
                INDEX IDX_12FB393E3C5110AB (data_survey_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE jecoute_data_answer_selected_choices (
                data_answer_id INT NOT NULL, 
                choice_id INT NOT NULL, 
                INDEX IDX_10DF117259C0831 (data_answer_id), 
                INDEX IDX_10DF117998666D1 (choice_id), 
                PRIMARY KEY(data_answer_id, choice_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB'
        );

        $this->addSql('ALTER TABLE jecoute_data_survey ADD CONSTRAINT FK_6579E8E7F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE jecoute_data_survey ADD CONSTRAINT FK_6579E8E7B3FE509D FOREIGN KEY (survey_id) REFERENCES jecoute_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jecoute_data_answer ADD CONSTRAINT FK_12FB393EA6DF29BA FOREIGN KEY (survey_question_id) REFERENCES jecoute_survey_question (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE jecoute_data_answer ADD CONSTRAINT FK_12FB393E3C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices ADD CONSTRAINT FK_10DF117259C0831 FOREIGN KEY (data_answer_id) REFERENCES jecoute_data_answer (id)');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices ADD CONSTRAINT FK_10DF117998666D1 FOREIGN KEY (choice_id) REFERENCES jecoute_choice (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_data_answer DROP FOREIGN KEY FK_12FB393E3C5110AB');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices DROP FOREIGN KEY FK_10DF117259C0831');
        $this->addSql('DROP TABLE jecoute_data_survey');
        $this->addSql('DROP TABLE jecoute_data_answer');
        $this->addSql('DROP TABLE jecoute_data_answer_selected_choices');
    }
}
