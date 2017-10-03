<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171003110301 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE referent (id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL, media_id BIGINT DEFAULT NULL, gender VARCHAR(6) NOT NULL, email_address VARCHAR(100) DEFAULT NULL, slug VARCHAR(100) NOT NULL, facebook_page_url VARCHAR(255) DEFAULT NULL, twitter_page_url VARCHAR(255) DEFAULT NULL, donation_page_url VARCHAR(255) DEFAULT NULL, website_url VARCHAR(255) DEFAULT NULL, geojson LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, display_media TINYINT(1) NOT NULL, managed_area_codes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', managed_area_marker_latitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', managed_area_marker_longitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', INDEX IDX_FE9AAC6CEA9FDD75 (media_id), UNIQUE INDEX referent_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE referent ADD CONSTRAINT FK_FE9AAC6CEA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE referent');
    }
}
