<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191023005244 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          jecoute_survey_question CHANGE from_suggested_question from_suggested_question INT DEFAULT NULL');

        $this->addSql(
            "UPDATE jecoute_survey_question AS sq 
            INNER JOIN jecoute_question AS q1 ON q1.id = sq.question_id
            INNER JOIN jecoute_question AS q2 ON q2.discr = 'suggested_question' AND q2.content = q1.content AND q2.`type` = q1.`type`
            SET sq.from_suggested_question = q2.id
            WHERE sq.from_suggested_question = 1;"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          jecoute_survey_question CHANGE from_suggested_question from_suggested_question TINYINT(1) DEFAULT \'0\' NOT NULL');

        $this->addSql(
            'UPDATE jecoute_survey_question AS sq 
            SET sq.from_suggested_question = 1
            WHERE sq.from_suggested_question IS NOT NULL'
        );
    }
}
