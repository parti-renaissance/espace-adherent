<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190321164328 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_managed_areas RENAME TO adherent_referent_data');

        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3EA9FDD75');
        $this->addSql('DROP INDEX IDX_562C7DA3EA9FDD75 ON adherents');
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
          linked_in_page_url VARCHAR(255) DEFAULT NULL, 
        ADD 
          slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherent_referent_data 
        ADD 
          CONSTRAINT FK_1A34CE40EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('CREATE INDEX IDX_1A34CE40EA9FDD75 ON adherent_referent_data (media_id)');

//        $this->addSql('UPDATE adherents a
//        JOIN referent r ON r.email_address = a.email_address AND r.first_name = a.first_name AND r.last_name = a.last_name
//        SET a.description       = r.description,
//            a.twitter_page_url  = r.twitter_page_url,
//            a.facebook_page_url = r.facebook_page_url,
//            a.media_id = r.media_id
//        WHERE a.managed_area_id > 0
//        ');
    }

    public function down(Schema $schema): void
    {
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
        $this->addSql('CREATE INDEX IDX_562C7DA3EA9FDD75 ON adherents (media_id)');

        $this->addSql('ALTER TABLE adherent_referent_data RENAME TO referent_managed_areas');
    }
}
