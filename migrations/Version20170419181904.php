<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170419181904 extends AbstractMigration
{
    private $adherents = [];

    public function preUp(Schema $schema)
    {
        foreach ($this->connection->fetchAll('SELECT DISTINCT candidate_id FROM legislative_candidates') as $record) {
            $this->adherents[] = $record['candidate_id'];
        }
    }

    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE legislative_district_zones (id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL, area_code VARCHAR(4) NOT NULL, area_type VARCHAR(20) NOT NULL, name VARCHAR(100) NOT NULL, keywords LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adherents ADD legislative_candidate TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE legislative_candidates DROP FOREIGN KEY FK_AE55AF9B91BD8781');
        $this->addSql('DROP INDEX UNIQ_AE55AF9B91BD8781 ON legislative_candidates');
        $this->addSql('ALTER TABLE legislative_candidates ADD district_zone_id SMALLINT UNSIGNED DEFAULT NULL, ADD media_id BIGINT DEFAULT NULL, ADD slug VARCHAR(100) NOT NULL, ADD gender VARCHAR(6) NOT NULL DEFAULT \'male\', ADD email_address VARCHAR(100) DEFAULT NULL, ADD twitter_page_url VARCHAR(255) DEFAULT NULL, ADD donation_page_url VARCHAR(255) DEFAULT NULL, ADD website_url VARCHAR(255) DEFAULT NULL, ADD district_name VARCHAR(100) NOT NULL, ADD district_number VARCHAR(10) NOT NULL, ADD latitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', ADD longitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', ADD description LONGTEXT DEFAULT NULL, ADD first_name VARCHAR(50) NOT NULL, ADD last_name VARCHAR(50) NOT NULL, ADD display_media TINYINT(1) NOT NULL, DROP candidate_id, CHANGE id id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE area facebook_page_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE legislative_candidates ADD CONSTRAINT FK_AE55AF9B23F5C396 FOREIGN KEY (district_zone_id) REFERENCES legislative_district_zones (id)');
        $this->addSql('ALTER TABLE legislative_candidates ADD CONSTRAINT FK_AE55AF9BEA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('CREATE INDEX IDX_AE55AF9B23F5C396 ON legislative_candidates (district_zone_id)');
        $this->addSql('CREATE INDEX IDX_AE55AF9BEA9FDD75 ON legislative_candidates (media_id)');
        $this->addSql('UPDATE legislative_candidates SET slug = id'); // fix for prod
        $this->addSql('CREATE UNIQUE INDEX legislative_candidates_slug_unique ON legislative_candidates (slug)');
        $this->addSql('CREATE UNIQUE INDEX legislative_district_zones_area_code_unique ON legislative_district_zones (area_code)');
    }

    public function postUp(Schema $schema)
    {
        if (\count($this->adherents)) {
            $this->connection->executeUpdate('UPDATE adherents SET legislative_candidate = 1 WHERE id IN(?)', [implode(',', $this->adherents)]);
        }

        $this->adherents = [];
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE legislative_candidates DROP FOREIGN KEY FK_AE55AF9B23F5C396');
        $this->addSql('DROP TABLE legislative_district_zones');
        $this->addSql('ALTER TABLE adherents DROP legislative_candidate');
        $this->addSql('ALTER TABLE legislative_candidates DROP FOREIGN KEY FK_AE55AF9BEA9FDD75');
        $this->addSql('DROP INDEX IDX_AE55AF9B23F5C396 ON legislative_candidates');
        $this->addSql('DROP INDEX IDX_AE55AF9BEA9FDD75 ON legislative_candidates');
        $this->addSql('DROP INDEX legislative_candidates_slug_unique ON legislative_candidates');
        $this->addSql('DROP INDEX legislative_district_zones_area_code_unique ON legislative_district_zones');
        $this->addSql('ALTER TABLE legislative_candidates ADD candidate_id INT UNSIGNED DEFAULT NULL, ADD area VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, DROP district_zone_id, DROP media_id, DROP slug, DROP facebook_page_url, DROP twitter_page_url, DROP donation_page_url, DROP website_url, DROP district_name, DROP district_number, DROP latitude, DROP longitude, DROP description, DROP first_name, DROP last_name, DROP display_media, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE legislative_candidates ADD CONSTRAINT FK_AE55AF9B91BD8781 FOREIGN KEY (candidate_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AE55AF9B91BD8781 ON legislative_candidates (candidate_id)');
    }
}
