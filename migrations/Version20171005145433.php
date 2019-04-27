<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171005145433 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE referent (id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL, media_id BIGINT DEFAULT NULL, gender VARCHAR(6) NOT NULL, email_address VARCHAR(100) DEFAULT NULL, slug VARCHAR(100) NOT NULL, facebook_page_url VARCHAR(255) DEFAULT NULL, twitter_page_url VARCHAR(255) DEFAULT NULL, geojson LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, area_label VARCHAR(255) NOT NULL, status VARCHAR(10) DEFAULT \'DISABLED\' NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, display_media TINYINT(1) NOT NULL, INDEX IDX_FE9AAC6CEA9FDD75 (media_id), UNIQUE INDEX referent_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referent_areas (referent_id SMALLINT UNSIGNED NOT NULL, area_id SMALLINT UNSIGNED NOT NULL, INDEX IDX_75CEBC6C35E47E35 (referent_id), INDEX IDX_75CEBC6CBD0F409C (area_id), PRIMARY KEY(referent_id, area_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referent_area (id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL, area_code VARCHAR(6) NOT NULL, area_type VARCHAR(20) NOT NULL, name VARCHAR(100) NOT NULL, keywords LONGTEXT NOT NULL, UNIQUE INDEX referent_area_area_code_unique (area_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE referent ADD CONSTRAINT FK_FE9AAC6CEA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE referent_areas ADD CONSTRAINT FK_75CEBC6C35E47E35 FOREIGN KEY (referent_id) REFERENCES referent (id)');
        $this->addSql('ALTER TABLE referent_areas ADD CONSTRAINT FK_75CEBC6CBD0F409C FOREIGN KEY (area_id) REFERENCES referent_area (id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE referent_areas DROP FOREIGN KEY FK_75CEBC6C35E47E35');
        $this->addSql('ALTER TABLE referent_areas DROP FOREIGN KEY FK_75CEBC6CBD0F409C');
        $this->addSql('DROP TABLE referent');
        $this->addSql('DROP TABLE referent_areas');
        $this->addSql('DROP TABLE referent_area');
    }
}
