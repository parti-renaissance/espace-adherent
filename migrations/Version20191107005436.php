<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191107005436 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE image (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          extension VARCHAR(10) NOT NULL, 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          UNIQUE INDEX UNIQ_C53D045FD17F50A6 (uuid), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          mooc 
        ADD 
          article_image_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          list_image_id INT UNSIGNED DEFAULT NULL, 
        DROP 
          image_name, 
          CHANGE youtube_id youtube_id VARCHAR(255) DEFAULT NULL, 
          CHANGE youtube_duration youtube_duration TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          mooc 
        ADD 
          CONSTRAINT FK_9D5D3B55684DD106 FOREIGN KEY (article_image_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE 
          mooc 
        ADD 
          CONSTRAINT FK_9D5D3B5543C8160D FOREIGN KEY (list_image_id) REFERENCES image (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9D5D3B55684DD106 ON mooc (article_image_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9D5D3B5543C8160D ON mooc (list_image_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc DROP FOREIGN KEY FK_9D5D3B55684DD106');
        $this->addSql('ALTER TABLE mooc DROP FOREIGN KEY FK_9D5D3B5543C8160D');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP INDEX UNIQ_9D5D3B55684DD106 ON mooc');
        $this->addSql('DROP INDEX UNIQ_9D5D3B5543C8160D ON mooc');
        $this->addSql('ALTER TABLE 
          mooc 
        ADD 
          image_name VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
        DROP 
          article_image_id, 
        DROP 
          list_image_id, 
          CHANGE youtube_id youtube_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
          CHANGE youtube_duration youtube_duration TIME NOT NULL');
    }
}
