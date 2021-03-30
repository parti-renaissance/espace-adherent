<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181219003017 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP FOREIGN KEY FK_18589988E2904019');
        $this->addSql('ALTER TABLE ideas_workshop_thread ADD uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE thread_id thread_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD CONSTRAINT FK_18589988E2904019 FOREIGN KEY (thread_id) REFERENCES ideas_workshop_thread (id)');
        $this->addSql('ALTER TABLE reports ADD idea_id INT UNSIGNED DEFAULT NULL, ADD thread_id INT UNSIGNED DEFAULT NULL, ADD thread_comment_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA7455B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id)');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA745E2904019 FOREIGN KEY (thread_id) REFERENCES ideas_workshop_thread (id)');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA7453A31E89B FOREIGN KEY (thread_comment_id) REFERENCES ideas_workshop_comment (id)');
        $this->addSql('CREATE INDEX IDX_F11FA7455B6FEF7D ON reports (idea_id)');
        $this->addSql('CREATE INDEX IDX_F11FA745E2904019 ON reports (thread_id)');
        $this->addSql('CREATE INDEX IDX_F11FA7453A31E89B ON reports (thread_comment_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA7455B6FEF7D');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745E2904019');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA7453A31E89B');
        $this->addSql('DROP INDEX IDX_F11FA7455B6FEF7D ON reports');
        $this->addSql('DROP INDEX IDX_F11FA745E2904019 ON reports');
        $this->addSql('DROP INDEX IDX_F11FA7453A31E89B ON reports');
        $this->addSql('ALTER TABLE reports DROP idea_id, DROP thread_id, DROP thread_comment_id');
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP FOREIGN KEY FK_18589988E2904019');
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP uuid, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE thread_id thread_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_thread DROP uuid, CHANGE id id INT AUTO_INCREMENT NOT NULL;');
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD CONSTRAINT FK_18589988E2904019 FOREIGN KEY (thread_id) REFERENCES ideas_workshop_thread (id)');
    }
}
