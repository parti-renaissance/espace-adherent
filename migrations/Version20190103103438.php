<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190103103438 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_question CHANGE published enabled TINYINT(1) NOT NULL, CHANGE guideline_id guideline_id INT NOT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_question ADD category VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_answer DROP FOREIGN KEY FK_256A5D7B1E27F6BF');
        $this->addSql('ALTER TABLE ideas_workshop_answer ADD CONSTRAINT FK_256A5D7B1E27F6BF FOREIGN KEY (question_id) REFERENCES ideas_workshop_question (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_question CHANGE guideline_id guideline_id INT DEFAULT NULL, CHANGE enabled published TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_question DROP category');
        $this->addSql('ALTER TABLE ideas_workshop_answer DROP FOREIGN KEY FK_256A5D7B1E27F6BF');
        $this->addSql('ALTER TABLE ideas_workshop_answer ADD CONSTRAINT FK_256A5D7B1E27F6BF FOREIGN KEY (question_id) REFERENCES ideas_workshop_question (id) ON DELETE CASCADE');
    }
}
