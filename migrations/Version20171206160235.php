<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171206160235 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('DROP TABLE citizen_project_feed_items');

        $this->addSql('CREATE TABLE citizen_project_comment (
                id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                citizen_project_id INT UNSIGNED NOT NULL, 
                author_id INT UNSIGNED DEFAULT NULL, 
                content LONGTEXT NOT NULL, 
                created_at DATETIME NOT NULL, 
                uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
                INDEX IDX_A57DD65FB3584533 (citizen_project_id), 
                INDEX IDX_A57DD65FF675F31B (author_id), PRIMARY KEY(id)
              ) 
             DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE citizen_project_comment ADD CONSTRAINT FK_A57DD65FB3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('ALTER TABLE citizen_project_comment ADD CONSTRAINT FK_A57DD65FF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE citizen_project_comment');

        $this->addSql('CREATE TABLE citizen_project_feed_items (id INT UNSIGNED AUTO_INCREMENT NOT NULL, citizen_project_id INT UNSIGNED DEFAULT NULL, author_id INT UNSIGNED DEFAULT NULL, event_id INT UNSIGNED DEFAULT NULL, item_type VARCHAR(18) NOT NULL COLLATE utf8_unicode_ci, content LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, published TINYINT(1) DEFAULT \'1\' NOT NULL, created_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', INDEX IDX_148F04E2B3584533 (citizen_project_id), INDEX IDX_148F04E2F675F31B (author_id), INDEX IDX_148F04E271F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE citizen_project_feed_items ADD CONSTRAINT FK_148F04E271F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citizen_project_feed_items ADD CONSTRAINT FK_148F04E2B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('ALTER TABLE citizen_project_feed_items ADD CONSTRAINT FK_148F04E2F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
    }
}
