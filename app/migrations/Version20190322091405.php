<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190322091405 extends AbstractMigration
{
    public function preUp(Schema $schema)
    {
        $this->connection->executeUpdate(
            'UPDATE adherent_message_filters AS f 
            INNER JOIN adherent_zone_filter_referent_tag AS ft ON ft.adherent_zone_filter_id = f.id
            SET f.referent_tag_id = ft.referent_tag_id 
            WHERE f.referent_tag_id IS NULL'
        );
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE adherent_zone_filter_referent_tag');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_zone_filter_referent_tag (
          adherent_zone_filter_id INT UNSIGNED NOT NULL, 
          referent_tag_id INT UNSIGNED NOT NULL, 
          INDEX IDX_9068D3599C262DB3 (referent_tag_id), 
          INDEX IDX_9068D359D2AD5954 (adherent_zone_filter_id), 
          PRIMARY KEY(
            adherent_zone_filter_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          adherent_zone_filter_referent_tag 
        ADD 
          CONSTRAINT FK_9068D359D2AD5954 FOREIGN KEY (adherent_zone_filter_id) REFERENCES adherent_message_filters (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          adherent_zone_filter_referent_tag 
        ADD 
          CONSTRAINT FK_B201503A9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
    }
}
