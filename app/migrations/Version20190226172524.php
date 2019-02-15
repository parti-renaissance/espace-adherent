<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190226172524 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ideas_workshop_answer_user_documents (
          ideas_workshop_answer_id INT NOT NULL, 
          user_document_id INT UNSIGNED NOT NULL, 
          INDEX IDX_824E75E79C97E9FB (ideas_workshop_answer_id), 
          INDEX IDX_824E75E76A24B1A2 (user_document_id), 
          PRIMARY KEY(
            ideas_workshop_answer_id, user_document_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          ideas_workshop_answer_user_documents 
        ADD 
          CONSTRAINT FK_824E75E79C97E9FB FOREIGN KEY (ideas_workshop_answer_id) REFERENCES ideas_workshop_answer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_answer_user_documents 
        ADD 
          CONSTRAINT FK_824E75E76A24B1A2 FOREIGN KEY (user_document_id) REFERENCES user_documents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA7453A31E89B');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA7455B6FEF7D');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745E2904019');
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
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ideas_workshop_answer_user_documents');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA7455B6FEF7D');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745E2904019');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA7453A31E89B');
        $this->addSql('ALTER TABLE 
          reports 
        ADD 
          CONSTRAINT FK_F11FA7455B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id)');
        $this->addSql('ALTER TABLE 
          reports 
        ADD 
          CONSTRAINT FK_F11FA745E2904019 FOREIGN KEY (thread_id) REFERENCES ideas_workshop_thread (id)');
        $this->addSql('ALTER TABLE 
          reports 
        ADD 
          CONSTRAINT FK_F11FA7453A31E89B FOREIGN KEY (thread_comment_id) REFERENCES ideas_workshop_comment (id)');
    }
}
