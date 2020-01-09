<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200214173130 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE nomenclature_senator_area (
          id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL, 
          code VARCHAR(6) NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          keywords LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', 
          UNIQUE INDEX senator_area_code_unique (code), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nomenclature_senator (
          id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL, 
          area_id SMALLINT UNSIGNED NOT NULL, 
          media_id BIGINT DEFAULT NULL, 
          gender VARCHAR(6) NOT NULL, 
          email_address VARCHAR(100) DEFAULT NULL, 
          slug VARCHAR(100) NOT NULL, 
          website_url VARCHAR(255) DEFAULT NULL, 
          facebook_page_url VARCHAR(255) DEFAULT NULL, 
          twitter_page_url VARCHAR(255) DEFAULT NULL, 
          description LONGTEXT DEFAULT NULL, 
          status VARCHAR(10) DEFAULT \'DISABLED\' NOT NULL, 
          first_name VARCHAR(50) NOT NULL, 
          last_name VARCHAR(50) NOT NULL, 
          display_media TINYINT(1) NOT NULL, 
          INDEX IDX_521C47AABD0F409C (area_id), 
          INDEX IDX_521C47AAEA9FDD75 (media_id), 
          UNIQUE INDEX senator_slug_unique (slug), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          nomenclature_senator 
        ADD 
          CONSTRAINT FK_521C47AABD0F409C FOREIGN KEY (area_id) REFERENCES nomenclature_senator_area (id)');
        $this->addSql('ALTER TABLE 
          nomenclature_senator 
        ADD 
          CONSTRAINT FK_521C47AAEA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE nomenclature_senator DROP FOREIGN KEY FK_521C47AABD0F409C');
        $this->addSql('DROP TABLE nomenclature_senator_area');
        $this->addSql('DROP TABLE nomenclature_senator');
    }
}
