<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190613133720 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE formation_files (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          article_id BIGINT DEFAULT NULL, 
          title VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          path VARCHAR(255) NOT NULL, 
          extension VARCHAR(255) NOT NULL, 
          INDEX IDX_70BEDE2C7294869C (article_id), 
          UNIQUE INDEX formation_file_slug_extension (slug, extension), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          formation_files 
        ADD 
          CONSTRAINT FK_70BEDE2C7294869C FOREIGN KEY (article_id) REFERENCES formation_articles (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE formation_files');
    }
}
