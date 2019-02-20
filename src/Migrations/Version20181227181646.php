<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181227181646 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_answer DROP FOREIGN KEY FK_256A5D7B1E27F6BF');
        $this->addSql('ALTER TABLE ideas_workshop_answer CHANGE question_id question_id INT NOT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_answer ADD CONSTRAINT FK_256A5D7B1E27F6BF FOREIGN KEY (question_id) REFERENCES ideas_workshop_question (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_answer DROP FOREIGN KEY FK_256A5D7B1E27F6BF');
        $this->addSql('ALTER TABLE ideas_workshop_answer CHANGE question_id question_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_answer ADD CONSTRAINT FK_256A5D7B1E27F6BF FOREIGN KEY (question_id) REFERENCES ideas_workshop_question (id)');
    }
}
