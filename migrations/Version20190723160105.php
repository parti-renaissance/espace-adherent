<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190723160105 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE municipal_event (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          organizer_id INT UNSIGNED DEFAULT NULL, 
          name VARCHAR(100) NOT NULL, 
          canonical_name VARCHAR(100) NOT NULL, 
          slug VARCHAR(130) NOT NULL, 
          description LONGTEXT NOT NULL, 
          time_zone VARCHAR(50) NOT NULL, 
          begin_at DATETIME NOT NULL, 
          finish_at DATETIME NOT NULL, 
          participants_count SMALLINT UNSIGNED NOT NULL, 
          status VARCHAR(20) NOT NULL, 
          published TINYINT(1) DEFAULT \'1\' NOT NULL, 
          capacity INT DEFAULT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          address_address VARCHAR(150) DEFAULT NULL, 
          address_postal_code VARCHAR(15) DEFAULT NULL, 
          address_city_insee VARCHAR(15) DEFAULT NULL, 
          address_city_name VARCHAR(255) DEFAULT NULL, 
          address_country VARCHAR(2) DEFAULT NULL, 
          address_region VARCHAR(255) DEFAULT NULL, 
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', 
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', 
          INDEX IDX_2A5B42D876C4DDA (organizer_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE municipal_event_referent_tag (
          municipal_event_id INT UNSIGNED NOT NULL, 
          referent_tag_id INT UNSIGNED NOT NULL, 
          INDEX IDX_63E512C370C12C5A (municipal_event_id), 
          INDEX IDX_63E512C39C262DB3 (referent_tag_id), 
          PRIMARY KEY(
            municipal_event_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          municipal_event 
        ADD 
          CONSTRAINT FK_2A5B42D876C4DDA FOREIGN KEY (organizer_id) REFERENCES adherents (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE 
          municipal_event_referent_tag 
        ADD 
          CONSTRAINT FK_63E512C370C12C5A FOREIGN KEY (municipal_event_id) REFERENCES municipal_event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          municipal_event_referent_tag 
        ADD 
          CONSTRAINT FK_63E512C39C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE municipal_event_referent_tag DROP FOREIGN KEY FK_63E512C370C12C5A');
        $this->addSql('DROP TABLE municipal_event');
        $this->addSql('DROP TABLE municipal_event_referent_tag');
    }
}
