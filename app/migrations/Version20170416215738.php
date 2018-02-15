<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170416215738 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE custom_search_results (id BIGINT AUTO_INCREMENT NOT NULL, media_id BIGINT DEFAULT NULL, title VARCHAR(100) NOT NULL, description VARCHAR(255) NOT NULL, keywords VARCHAR(255) DEFAULT NULL, url VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, display_media TINYINT(1) NOT NULL, INDEX IDX_38973E54EA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE custom_search_results ADD CONSTRAINT FK_38973E54EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE custom_search_results');
    }
}
