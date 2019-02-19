<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190219112556 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE citizen_project_comment');
        $this->addSql('ALTER TABLE citizen_projects DROP coordinator_comment');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE citizen_project_comment (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          citizen_project_id INT UNSIGNED NOT NULL, 
          author_id INT UNSIGNED DEFAULT NULL, 
          content LONGTEXT NOT NULL COLLATE utf8_unicode_ci, 
          created_at DATETIME NOT NULL, 
          uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', 
          INDEX IDX_A57DD65FB3584533 (citizen_project_id), 
          INDEX IDX_A57DD65FF675F31B (author_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          citizen_project_comment 
        ADD 
          CONSTRAINT FK_A57DD65FB3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('ALTER TABLE 
          citizen_project_comment 
        ADD 
          CONSTRAINT FK_A57DD65FF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE 
          citizen_projects 
        ADD 
          coordinator_comment LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP last_contribution_notification_date');
    }
}
