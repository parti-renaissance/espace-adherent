<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181226122053 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_vote DROP FOREIGN KEY FK_9A9B53535B6FEF7D');
        $this->addSql('ALTER TABLE ideas_workshop_vote ADD CONSTRAINT FK_9A9B53535B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP FOREIGN KEY FK_18589988E2904019');
        $this->addSql('ALTER TABLE ideas_workshop_comment CHANGE thread_id thread_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD CONSTRAINT FK_18589988E2904019 FOREIGN KEY (thread_id) REFERENCES ideas_workshop_thread (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ideas_workshop_answer DROP FOREIGN KEY FK_256A5D7B5B6FEF7D');
        $this->addSql('ALTER TABLE ideas_workshop_answer CHANGE idea_id idea_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_answer ADD CONSTRAINT FK_256A5D7B5B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_answer DROP FOREIGN KEY FK_256A5D7B5B6FEF7D');
        $this->addSql('ALTER TABLE ideas_workshop_answer CHANGE idea_id idea_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_answer ADD CONSTRAINT FK_256A5D7B5B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id)');
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP FOREIGN KEY FK_18589988E2904019');
        $this->addSql('ALTER TABLE ideas_workshop_comment CHANGE thread_id thread_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD CONSTRAINT FK_18589988E2904019 FOREIGN KEY (thread_id) REFERENCES ideas_workshop_thread (id)');
        $this->addSql('ALTER TABLE ideas_workshop_vote DROP FOREIGN KEY FK_9A9B53535B6FEF7D');
        $this->addSql('ALTER TABLE ideas_workshop_vote ADD CONSTRAINT FK_9A9B53535B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id)');
    }
}
