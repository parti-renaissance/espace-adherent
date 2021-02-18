<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20210218143353 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cause (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          coalition_id INT UNSIGNED NOT NULL, 
          author_id INT UNSIGNED NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          description LONGTEXT NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          image_name VARCHAR(255) DEFAULT NULL, 
          INDEX IDX_F0DA7FBFC2A46A23 (coalition_id), 
          INDEX IDX_F0DA7FBFF675F31B (author_id), 
          UNIQUE INDEX cause_uuid_unique (uuid), 
          UNIQUE INDEX cause_name_unique (name), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          cause 
        ADD 
          CONSTRAINT FK_F0DA7FBFC2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id)');
        $this->addSql('ALTER TABLE 
          cause 
        ADD 
          CONSTRAINT FK_F0DA7FBFF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE events ADD cause_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          events 
        ADD 
          CONSTRAINT FK_5387574A66E2221E FOREIGN KEY (cause_id) REFERENCES cause (id)');
        $this->addSql('CREATE INDEX IDX_5387574A66E2221E ON events (cause_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A66E2221E');
        $this->addSql('DROP TABLE cause');
        $this->addSql('DROP INDEX IDX_5387574A66E2221E ON events');
        $this->addSql('ALTER TABLE events DROP cause_id');
    }
}
