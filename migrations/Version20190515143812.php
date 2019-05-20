<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190515143812 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE referent_user_filter_referent_tag (
          referent_user_filter_id INT UNSIGNED NOT NULL, 
          referent_tag_id INT UNSIGNED NOT NULL, 
          INDEX IDX_F2BB20FEEFAB50C4 (referent_user_filter_id), 
          INDEX IDX_F2BB20FE9C262DB3 (referent_tag_id), 
          PRIMARY KEY(
            referent_user_filter_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE 
          referent_user_filter_referent_tag 
        ADD 
          CONSTRAINT FK_F2BB20FEEFAB50C4 FOREIGN KEY (referent_user_filter_id) REFERENCES adherent_message_filters (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE 
          referent_user_filter_referent_tag 
        ADD 
          CONSTRAINT FK_F2BB20FE9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');

        $this->addSql(
            'INSERT INTO referent_user_filter_referent_tag (referent_user_filter_id, referent_tag_id)
            SELECT id, referent_tag_id 
            FROM adherent_message_filters AS a
            WHERE a.dtype = \'referentuserfilter\' AND a.referent_tag_id IS NOT NULL'
        );

        $this->addSql(
            'UPDATE adherent_message_filters SET referent_tag_id = NULL
            WHERE dtype = \'referentuserfilter\''
        );

        $this->addSql('CREATE TABLE mailchimp_campaign (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          message_id INT UNSIGNED DEFAULT NULL, 
          external_id VARCHAR(255) DEFAULT NULL, 
          synchronized TINYINT(1) DEFAULT \'0\' NOT NULL,
          recipient_count INT DEFAULT NULL, 
          status VARCHAR(255) NOT NULL,
          detail VARCHAR(255) DEFAULT NULL,
          label VARCHAR(255) DEFAULT NULL,
          static_segment_id VARCHAR(255) DEFAULT NULL,
          INDEX IDX_CFABD309537A1329 (message_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE 
          mailchimp_campaign 
        ADD 
          CONSTRAINT FK_CFABD309537A1329 FOREIGN KEY (message_id) REFERENCES adherent_messages (id)');

        $this->addSql(
            'INSERT INTO mailchimp_campaign (message_id, external_id, synchronized, recipient_count, status)
            SELECT id, external_id, synchronized, recipient_count, `status` FROM adherent_messages'
        );

        $this->addSql('ALTER TABLE adherent_messages DROP synchronized, DROP recipient_count, DROP external_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'UPDATE adherent_message_filters AS f 
            INNER JOIN referent_user_filter_referent_tag AS r ON r.referent_user_filter_id = f.id
            SET f.referent_tag_id = r.referent_tag_id'
        );

        $this->addSql('DROP TABLE referent_user_filter_referent_tag');

        $this->addSql('ALTER TABLE 
          adherent_messages 
        ADD 
          synchronized TINYINT(1) DEFAULT \'0\' NOT NULL, 
        ADD 
          recipient_count INT DEFAULT NULL,
        ADD 
          external_id VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');

        $this->addSql(
            'UPDATE adherent_messages AS m
            INNER JOIN mailchimp_campaign AS c ON c.message_id = m.id
            SET 
                m.synchronized = c.synchronized, 
                m.recipient_count = c.recipient_count, 
                m.external_id = c.external_id'
        );

        $this->addSql('DROP TABLE mailchimp_campaign');
    }
}
