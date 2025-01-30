<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220706000352 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_message_filter_zone (
          message_filter_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_64171C02B92CB468 (message_filter_id),
          INDEX IDX_64171C029F2C3FAB (zone_id),
          PRIMARY KEY(message_filter_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          adherent_message_filter_zone
        ADD
          CONSTRAINT FK_64171C02B92CB468 FOREIGN KEY (message_filter_id) REFERENCES adherent_message_filters (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_message_filter_zone
        ADD
          CONSTRAINT FK_64171C029F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mailchimp_campaign ADD zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          mailchimp_campaign
        ADD
          CONSTRAINT FK_CFABD3099F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_CFABD3099F2C3FAB ON mailchimp_campaign (zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE adherent_message_filter_zone');
        $this->addSql('ALTER TABLE mailchimp_campaign DROP FOREIGN KEY FK_CFABD3099F2C3FAB');
        $this->addSql('DROP INDEX IDX_CFABD3099F2C3FAB ON mailchimp_campaign');
        $this->addSql('ALTER TABLE mailchimp_campaign DROP zone_id');
    }
}
