<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210218163319 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cause (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          coalition_id INT UNSIGNED NOT NULL, 
          author_id INT UNSIGNED DEFAULT NULL, 
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
          CONSTRAINT FK_F0DA7FBFF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cause');
    }
}
