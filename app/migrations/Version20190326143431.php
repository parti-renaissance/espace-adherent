<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190326143431 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_managed_areas RENAME TO adherent_referent_data');

        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3EA9FDD75');
        $this->addSql('DROP INDEX IDX_562C7DA3EA9FDD75 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3DC184E71');
        $this->addSql('DROP INDEX UNIQ_562C7DA3DC184E71 ON adherents');
        $this->addSql('ALTER TABLE adherents CHANGE managed_area_id adherent_referent_data_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA36216469F FOREIGN KEY (adherent_referent_data_id) REFERENCES adherent_referent_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA36216469F ON adherents (adherent_referent_data_id)');

        $this->addSql('ALTER TABLE 
          adherents 
        DROP 
          media_id, 
        DROP 
          description, 
        DROP 
          facebook_page_url, 
        DROP 
          twitter_page_url, 
        DROP 
          display_media');

        $this->addSql('ALTER TABLE 
          adherent_referent_data 
        ADD 
          media_id BIGINT DEFAULT NULL, 
        ADD 
          display_media TINYINT(1) NOT NULL, 
        ADD 
          description LONGTEXT DEFAULT NULL, 
        ADD 
          facebook_page_url VARCHAR(255) DEFAULT NULL, 
        ADD 
          twitter_page_url VARCHAR(255) DEFAULT NULL, 
        ADD 
          linked_in_page_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherent_referent_data 
        ADD 
          CONSTRAINT FK_1A34CE40EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('CREATE INDEX IDX_1A34CE40EA9FDD75 ON adherent_referent_data (media_id)');

        $this->addSql('ALTER TABLE adherents ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3989D9B62 ON adherents (slug)');

        $this->addSql('ALTER TABLE adherent_referent_data ADD tags_label VARCHAR(255) DEFAULT NULL');

        $this->addSql('ALTER TABLE referent_tags ADD category VARCHAR(255) DEFAULT NULL');

        $this->addSql('UPDATE referent_tags SET category = "departement" WHERE code REGEXP "^[0-9]{1,2}[:.,-]?$" OR code IN ("2A", "2B")');
        $this->addSql('UPDATE referent_tags SET category = "arrondissement" WHERE code REGEXP "^[0-9]{5}[:.,-]?$"');
        $this->addSql('UPDATE referent_tags SET category = "region" WHERE code REGEXP "^[A-Z]{2}[:.,-]?$"');
        $this->addSql('UPDATE referent_tags SET category = "circo" WHERE code REGEXP "^CIRCO_?"');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_tags DROP category');

        $this->addSql('ALTER TABLE adherent_referent_data DROP tags_label');

        $this->addSql('DROP INDEX UNIQ_562C7DA3989D9B62 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP slug');

        $this->addSql('ALTER TABLE adherent_referent_data DROP FOREIGN KEY FK_1A34CE40EA9FDD75');
        $this->addSql('DROP INDEX IDX_1A34CE40EA9FDD75 ON adherent_referent_data');
        $this->addSql('ALTER TABLE 
          adherent_referent_data 
        DROP 
          media_id, 
        DROP 
          display_media, 
        DROP 
          description, 
        DROP 
          facebook_page_url, 
        DROP 
          twitter_page_url, 
        DROP 
          linked_in_page_url, 
        DROP 
          slug');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          media_id BIGINT DEFAULT NULL, 
        ADD 
          description LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, 
        ADD 
          facebook_page_url VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
        ADD 
          twitter_page_url VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
        ADD 
          display_media TINYINT(1) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');

        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA36216469F');
        $this->addSql('DROP INDEX UNIQ_562C7DA36216469F ON adherents');
        $this->addSql('ALTER TABLE adherents CHANGE adherent_referent_data_id managed_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3DC184E71 FOREIGN KEY (managed_area_id) REFERENCES adherent_referent_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3DC184E71 ON adherents (managed_area_id)');

        $this->addSql('CREATE INDEX IDX_562C7DA3EA9FDD75 ON adherents (media_id)');

        $this->addSql('ALTER TABLE adherent_referent_data RENAME TO referent_managed_areas');
    }
}
