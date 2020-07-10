<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200710145133 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE referent_elected_representative_filter_referent_tag (
          referent_elected_representative_filter_id INT UNSIGNED NOT NULL, 
          referent_tag_id INT UNSIGNED NOT NULL, 
          INDEX IDX_5293915CFAAA22F5 (
            referent_elected_representative_filter_id
          ), 
          INDEX IDX_5293915C9C262DB3 (referent_tag_id), 
          PRIMARY KEY(
            referent_elected_representative_filter_id, 
            referent_tag_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          referent_elected_representative_filter_referent_tag 
        ADD 
          CONSTRAINT FK_5293915CFAAA22F5 FOREIGN KEY (
            referent_elected_representative_filter_id
          ) REFERENCES adherent_message_filters (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          referent_elected_representative_filter_referent_tag 
        ADD 
          CONSTRAINT FK_5293915C9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          mandates JSON DEFAULT NULL, 
        ADD 
          political_functions JSON DEFAULT NULL, 
        ADD 
          labels JSON DEFAULT NULL, 
        ADD 
          user_list_definitions JSON DEFAULT NULL, 
        ADD 
          is_adherent LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE mailchimp_campaign ADD mailchimp_segment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          mailchimp_campaign 
        ADD 
          CONSTRAINT FK_CFABD309D21E482E FOREIGN KEY (mailchimp_segment_id) REFERENCES mailchimp_segment (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_CFABD309D21E482E ON mailchimp_campaign (mailchimp_segment_id)');
        $this->addSql('ALTER TABLE mailchimp_segment CHANGE external_id external_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE referent_elected_representative_filter_referent_tag');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        DROP 
          mandates, 
        DROP 
          political_functions, 
        DROP 
          labels, 
        DROP 
          user_list_definitions, 
        DROP 
          is_adherent');
        $this->addSql('ALTER TABLE mailchimp_campaign DROP FOREIGN KEY FK_CFABD309D21E482E');
        $this->addSql('DROP INDEX IDX_CFABD309D21E482E ON mailchimp_campaign');
        $this->addSql('ALTER TABLE mailchimp_campaign DROP mailchimp_segment_id');
        $this->addSql('ALTER TABLE 
          mailchimp_segment CHANGE external_id external_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
