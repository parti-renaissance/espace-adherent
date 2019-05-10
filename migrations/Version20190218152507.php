<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190218152507 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('RENAME TABLE referent_zone_filter_referent_tag TO adherent_zone_filter_referent_tag');

        $this->addSql('ALTER TABLE adherent_zone_filter_referent_tag DROP FOREIGN KEY FK_B201503A61DD8E55');
        $this->addSql('DROP INDEX IDX_B201503A61DD8E55 ON adherent_zone_filter_referent_tag');
        $this->addSql('ALTER TABLE adherent_zone_filter_referent_tag DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE 
          adherent_zone_filter_referent_tag CHANGE referent_zone_filter_id adherent_zone_filter_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE 
          adherent_zone_filter_referent_tag 
        ADD 
          CONSTRAINT FK_9068D359D2AD5954 FOREIGN KEY (adherent_zone_filter_id) REFERENCES adherent_message_filters (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9068D359D2AD5954 ON adherent_zone_filter_referent_tag (adherent_zone_filter_id)');
        $this->addSql('ALTER TABLE 
          adherent_zone_filter_referent_tag 
        ADD 
          PRIMARY KEY (
            adherent_zone_filter_id, referent_tag_id
          )');
        $this->addSql('ALTER TABLE 
          adherent_zone_filter_referent_tag RENAME INDEX idx_b201503a9c262db3 TO IDX_9068D3599C262DB3');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_zone_filter_referent_tag DROP FOREIGN KEY FK_9068D359D2AD5954');
        $this->addSql('DROP INDEX IDX_9068D359D2AD5954 ON adherent_zone_filter_referent_tag');
        $this->addSql('ALTER TABLE adherent_zone_filter_referent_tag DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE 
          adherent_zone_filter_referent_tag CHANGE adherent_zone_filter_id referent_zone_filter_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE 
          adherent_zone_filter_referent_tag 
        ADD 
          CONSTRAINT FK_B201503A61DD8E55 FOREIGN KEY (referent_zone_filter_id) REFERENCES adherent_message_filters (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B201503A61DD8E55 ON adherent_zone_filter_referent_tag (referent_zone_filter_id)');
        $this->addSql('ALTER TABLE 
          adherent_zone_filter_referent_tag 
        ADD 
          PRIMARY KEY (
            referent_zone_filter_id, referent_tag_id
          )');
        $this->addSql('ALTER TABLE 
          adherent_zone_filter_referent_tag RENAME INDEX idx_9068d3599c262db3 TO IDX_B201503A9C262DB3');

        $this->addSql('RENAME TABLE adherent_zone_filter_referent_tag TO referent_zone_filter_referent_tag');
    }
}
