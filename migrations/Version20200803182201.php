<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200803182201 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mailchimp_campaign_mailchimp_segment (
          mailchimp_campaign_id INT UNSIGNED NOT NULL, 
          mailchimp_segment_id INT NOT NULL, 
          INDEX IDX_901CE107828112CC (mailchimp_campaign_id), 
          INDEX IDX_901CE107D21E482E (mailchimp_segment_id), 
          PRIMARY KEY(
            mailchimp_campaign_id, mailchimp_segment_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          mailchimp_campaign_mailchimp_segment 
        ADD 
          CONSTRAINT FK_901CE107828112CC FOREIGN KEY (mailchimp_campaign_id) REFERENCES mailchimp_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          mailchimp_campaign_mailchimp_segment 
        ADD 
          CONSTRAINT FK_901CE107D21E482E FOREIGN KEY (mailchimp_segment_id) REFERENCES mailchimp_segment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          mandate VARCHAR(255) DEFAULT NULL, 
        ADD 
          political_function VARCHAR(255) DEFAULT NULL, 
        ADD 
          label VARCHAR(255) DEFAULT NULL, 
        ADD 
          user_list_definition VARCHAR(255) DEFAULT NULL, 
        ADD 
          is_adherent TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE mailchimp_segment CHANGE external_id external_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE mailchimp_campaign_mailchimp_segment');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        DROP 
          mandate, 
        DROP 
          political_function, 
        DROP 
          label, 
        DROP 
          user_list_definition, 
        DROP 
          is_adherent');
        $this->addSql('ALTER TABLE 
          mailchimp_segment CHANGE external_id external_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
