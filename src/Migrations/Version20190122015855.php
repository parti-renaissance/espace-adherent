<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190122015855 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_message_filters (id INT UNSIGNED AUTO_INCREMENT NOT NULL, referent_tag_id INT UNSIGNED DEFAULT NULL, synchronized TINYINT(1) DEFAULT \'0\' NOT NULL, dtype VARCHAR(255) NOT NULL, include_adherents_no_committee TINYINT(1) DEFAULT NULL, include_adherents_in_committee TINYINT(1) DEFAULT NULL, include_committee_supervisors TINYINT(1) DEFAULT NULL, include_committee_hosts TINYINT(1) DEFAULT NULL, include_citizen_project_hosts TINYINT(1) DEFAULT NULL, gender VARCHAR(255) DEFAULT NULL, age_min INT DEFAULT NULL, age_max INT DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, interests JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\', INDEX IDX_28CA9F949C262DB3 (referent_tag_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referent_zone_filter_referent_tag (referent_zone_filter_id INT UNSIGNED NOT NULL, referent_tag_id INT UNSIGNED NOT NULL, INDEX IDX_B201503A61DD8E55 (referent_zone_filter_id), INDEX IDX_B201503A9C262DB3 (referent_tag_id), PRIMARY KEY(referent_zone_filter_id, referent_tag_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adherent_message_filters ADD CONSTRAINT FK_28CA9F949C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('ALTER TABLE referent_zone_filter_referent_tag ADD CONSTRAINT FK_B201503A61DD8E55 FOREIGN KEY (referent_zone_filter_id) REFERENCES adherent_message_filters (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE referent_zone_filter_referent_tag ADD CONSTRAINT FK_B201503A9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherent_messages ADD filter_id INT UNSIGNED DEFAULT NULL, DROP filter');
        $this->addSql('ALTER TABLE adherent_messages ADD CONSTRAINT FK_D187C183D395B25E FOREIGN KEY (filter_id) REFERENCES adherent_message_filters (id)');
        $this->addSql('CREATE INDEX IDX_D187C183D395B25E ON adherent_messages (filter_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_messages DROP FOREIGN KEY FK_D187C183D395B25E');
        $this->addSql('ALTER TABLE referent_zone_filter_referent_tag DROP FOREIGN KEY FK_B201503A61DD8E55');
        $this->addSql('DROP TABLE adherent_message_filters');
        $this->addSql('DROP TABLE referent_zone_filter_referent_tag');
        $this->addSql('DROP INDEX IDX_D187C183D395B25E ON adherent_messages');
        $this->addSql('ALTER TABLE adherent_messages ADD filter LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', DROP filter_id');
    }
}
